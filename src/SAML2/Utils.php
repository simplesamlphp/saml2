<?php

declare(strict_types=1);

namespace SAML2;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Compat\AbstractContainer;
use SAML2\Compat\ContainerSingleton;
use SAML2\Exception\RuntimeException;
use SAML2\Utils\XPath;
use SAML2\XML\ds\KeyInfo;
use SAML2\XML\ds\X509Certificate;
use SAML2\XML\ds\X509Data;
use SAML2\XML\ds\KeyName;
use SAML2\XML\md\KeyDescriptor;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\Exception\NoSignatureFoundException;
use SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException;

use function count;
use function in_array;
use function openssl_pkey_get_details;
use function serialize;
use function sha1;
use function strlen;
use function str_pad;
use function str_replace;
use function strtolower;
use function strval;
use function substr;
use function trim;
use function var_export;

/**
 * Helper functions for the SAML2 library.
 *
 * @package SimpleSAMLphp
 */
class Utils
{
    /**
     * Check the Signature in a XML element.
     *
     * This function expects the XML element to contain a Signature element
     * which contains a reference to the XML-element. This is common for both
     * messages and assertions.
     *
     * Note that this function only validates the element itself. It does not
     * check this against any local keys.
     *
     * If no Signature-element is located, this function will return false. All
     * other validation errors result in an exception. On successful validation
     * an array will be returned. This array contains the information required to
     * check the signature against a public key.
     *
     * @param \DOMElement $root The element which should be validated.
     * @throws \Exception
     * @return array|false An array with information about the Signature element.
     */
    public static function validateElement(DOMElement $root): array|false
    {
        /* Create an XML security object. */
        $objXMLSecDSig = new XMLSecurityDSig();

        /* Both SAML messages and SAML assertions use the 'ID' attribute. */
        $objXMLSecDSig->idKeys[] = 'ID';

        $xpCache = XPath::getXPath($root);
        /* Locate the XMLDSig Signature element to be used. */
        /** @var \DOMElement[] $signatureElement */
        $signatureElement = XPath::xpQuery($root, './ds:Signature', $xpCache);
        if (empty($signatureElement)) {
            /* We don't have a signature element ot validate. */

            return false;
        } elseif (count($signatureElement) > 1) {
            throw new TooManyElementsException('XMLSec: more than one signature element in root.');
        }
        $signatureElement = $signatureElement[0];
        $objXMLSecDSig->sigNode = $signatureElement;

        /* Canonicalize the XMLDSig SignedInfo element in the message. */
        $objXMLSecDSig->canonicalizeSignedInfo();

        /* Validate referenced xml nodes. */
        if (!$objXMLSecDSig->validateReference()) {
            throw new Exception('XMLsec: digest validation failed');
        }

        /* Check that $root is one of the signed nodes. */
        $rootSigned = false;
        /** @var \DOMNode $signedNode */
        foreach ($objXMLSecDSig->getValidatedNodes() as $signedNode) {
            if ($signedNode->isSameNode($root)) {
                $rootSigned = true;
                break;
            } elseif ($root->parentNode instanceof DOMDocument && $signedNode->isSameNode($root->ownerDocument)) {
                /* $root is the root element of a signed document. */
                $rootSigned = true;
                break;
            }
        }
        if (!$rootSigned) {
            throw new NoSignatureFoundException('XMLSec: The root element is not signed.');
        }

        /* Now we extract all available X509 certificates in the signature element. */
        $xpCache = XPath::getXPath($signatureElement);
        $certificates = [];
        $certNodes = XPath::xpQuery($signatureElement, './ds:KeyInfo/ds:X509Data/ds:X509Certificate', $xpCache);
        foreach ($certNodes as $certNode) {
            $certData = trim($certNode->textContent);
            $certData = str_replace(["\r", "\n", "\t", ' '], '', $certData);
            $certificates[] = $certData;
        }

        $ret = [
            'Signature' => $objXMLSecDSig,
            'Certificates' => $certificates,
        ];

        return $ret;
    }


    /**
     * Helper function to convert a XMLSecurityKey to the correct algorithm.
     *
     * @param XMLSecurityKey $key The key.
     * @param string $algorithm The desired algorithm.
     * @param string $type Public or private key, defaults to public.
     * @return XMLSecurityKey The new key.
     */
    public static function castKey(XMLSecurityKey $key, string $algorithm, string $type = null): XMLSecurityKey
    {
        $type = $type ?: 'public';
        Assert::oneOf($type, ["private", "public"]);

        // do nothing if algorithm is already the type of the key
        if ($key->type === $algorithm) {
            return $key;
        }

        if (
            !in_array($algorithm, [
                XMLSecurityKey::RSA_1_5,
                XMLSecurityKey::RSA_SHA1,
                XMLSecurityKey::RSA_SHA256,
                XMLSecurityKey::RSA_SHA384,
                XMLSecurityKey::RSA_SHA512
            ], true)
        ) {
            throw new Exception('Unsupported signing algorithm.');
        }

        /** @psalm-suppress PossiblyNullArgument */
        $keyInfo = openssl_pkey_get_details($key->key);
        if ($keyInfo === false) {
            throw new Exception('Unable to get key details from XMLSecurityKey.');
        }
        if (!isset($keyInfo['key'])) {
            throw new Exception('Missing key in public key details.');
        }

        $newKey = new XMLSecurityKey($algorithm, ['type' => $type]);
        $newKey->loadKey($keyInfo['key']);

        return $newKey;
    }


    /**
     * Check a signature against a key.
     *
     * An exception is thrown if we are unable to validate the signature.
     *
     * @param array $info The information returned by the validateElement() function.
     * @param XMLSecurityKey $key The publickey that should validate the Signature object.
     * @throws \Exception
     * @return void
     */
    public static function validateSignature(array $info, XMLSecurityKey $key): void
    {
        Assert::keyExists($info, "Signature");

        /** @var XMLSecurityDSig $objXMLSecDSig */
        $objXMLSecDSig = $info['Signature'];

        $xpCache = XPath::getXPath($objXMLSecDSig->sigNode);
        /**
         * @var \DOMElement[] $sigMethod
         * @var \DOMElement $objXMLSecDSig->sigNode
         */
        $sigMethod = XPath::xpQuery($objXMLSecDSig->sigNode, './ds:SignedInfo/ds:SignatureMethod', $xpCache);
        if (empty($sigMethod)) {
            throw new MissingElementException('Missing SignatureMethod element.');
        }
        $sigMethod = $sigMethod[0];
        if (!$sigMethod->hasAttribute('Algorithm')) {
            throw new MissingAttributeException('Missing Algorithm-attribute on SignatureMethod element.');
        }
        $algo = $sigMethod->getAttribute('Algorithm');

        if ($key->type === XMLSecurityKey::RSA_SHA256 && $algo !== $key->type) {
            $key = self::castKey($key, $algo);
        }

        /* Check the signature. */
        if ($objXMLSecDSig->verify($key) !== 1) {
            throw new SignatureVerificationFailedException("Unable to validate Signature");
        }
    }


    /**
     * Make an exact copy the specific \DOMElement.
     *
     * @param \DOMElement $element The element we should copy.
     * @param \DOMElement|null $parent The target parent element.
     * @return \DOMElement The copied element.
     */
    public static function copyElement(DOMElement $element, DOMElement $parent = null): DOMElement
    {
        if ($parent === null) {
            $document = DOMDocumentFactory::create();
        } else {
            $document = $parent->ownerDocument;
        }

        $namespaces = [];
        for ($e = $element; $e instanceof DOMNode; $e = $e->parentNode) {
            $xpCache = XPath::getXPath($e);
            foreach (XPath::xpQuery($e, './namespace::*', $xpCache) as $ns) {
                $prefix = $ns->localName;
                if ($prefix === 'xml' || $prefix === 'xmlns') {
                    continue;
                }
                $uri = $ns->nodeValue;
                if (!isset($namespaces[$prefix])) {
                    $namespaces[$prefix] = $uri;
                }
            }
        }

        /** @var \DOMElement $newElement */
        $newElement = $document->importNode($element, true);
        if ($parent !== null) {
            /* We need to append the child to the parent before we add the namespaces. */
            $parent->appendChild($newElement);
        }

        foreach ($namespaces as $prefix => $uri) {
            $newElement->setAttributeNS($uri, $prefix . ':__ns_workaround__', 'tmp');
            $newElement->removeAttributeNS($uri, '__ns_workaround__');
        }

        return $newElement;
    }


    /**
     * Parse a boolean attribute.
     *
     * @param \DOMElement $node The element we should fetch the attribute from.
     * @param string $attributeName The name of the attribute.
     * @param mixed|null $default The value that should be returned if the attribute doesn't exist.
     * @return bool|mixed The value of the attribute, or $default if the attribute doesn't exist.
     */
    public static function parseBoolean(DOMElement $node, string $attributeName, $default = null)
    {
        if (!$node->hasAttribute($attributeName)) {
            return $default;
        }
        $value = $node->getAttribute($attributeName);
        switch (strtolower($value)) {
            case '0':
            case 'false':
                return false;
            case '1':
            case 'true':
                return true;
            default:
                throw new Exception('Invalid value of boolean attribute ' . var_export($attributeName, true)
                    . ': ' . var_export($value, true));
        }
    }


    /**
     * Insert a Signature node.
     *
     * @param XMLSecurityKey $key The key we should use to sign the message.
     * @param array $certificates The certificates we should add to the signature node.
     * @param \DOMElement $root The XML node we should sign.
     * @param \DOMNode $insertBefore  The XML element we should insert the signature element before.
     * @return void
     */
    public static function insertSignature(
        XMLSecurityKey $key,
        array $certificates,
        DOMElement $root,
        DOMNode $insertBefore = null
    ): void {
        $objXMLSecDSig = new XMLSecurityDSig();
        $objXMLSecDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        switch ($key->type) {
            case XMLSecurityKey::RSA_SHA256:
                $type = XMLSecurityDSig::SHA256;
                break;
            case XMLSecurityKey::RSA_SHA384:
                $type = XMLSecurityDSig::SHA384;
                break;
            case XMLSecurityKey::RSA_SHA512:
                $type = XMLSecurityDSig::SHA512;
                break;
            default:
                $type = XMLSecurityDSig::SHA1;
        }

        $objXMLSecDSig->addReferenceList(
            [$root],
            $type,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature', XMLSecurityDSig::EXC_C14N],
            ['id_name' => 'ID', 'overwrite' => false]
        );

        $objXMLSecDSig->sign($key);

        foreach ($certificates as $certificate) {
            $objXMLSecDSig->add509Cert($certificate, true);
        }

        $objXMLSecDSig->insertSignature($root, $insertBefore);
    }


    /**
     * Decrypt an encrypted element.
     *
     * This is an internal helper function.
     *
     * @param \DOMElement $encryptedData The encrypted data.
     * @param XMLSecurityKey $inputKey The decryption key.
     * @param array &$blacklist Blacklisted decryption algorithms.
     * @throws \Exception
     * @return \DOMElement The decrypted element.
     */
    private static function doDecryptElement(
        DOMElement $encryptedData,
        XMLSecurityKey $inputKey,
        array &$blacklist
    ): DOMElement {
        $enc = new XMLSecEnc();

        $enc->setNode($encryptedData);
        $enc->type = $encryptedData->getAttribute("Type");

        $symmetricKey = $enc->locateKey($encryptedData);
        if (!$symmetricKey) {
            throw new Exception('Could not locate key algorithm in encrypted data.');
        }

        $symmetricKeyInfo = $enc->locateKeyInfo($symmetricKey);
        if (!$symmetricKeyInfo) {
            throw new Exception('Could not locate <dsig:KeyInfo> for the encrypted key.');
        }

        $inputKeyAlgo = $inputKey->getAlgorithm();
        if ($symmetricKeyInfo->isEncrypted) {
            $symKeyInfoAlgo = $symmetricKeyInfo->getAlgorithm();

            if (in_array($symKeyInfoAlgo, $blacklist, true)) {
                throw new Exception('Algorithm disabled: ' . var_export($symKeyInfoAlgo, true));
            }

            if ($symKeyInfoAlgo === XMLSecurityKey::RSA_OAEP_MGF1P && $inputKeyAlgo === XMLSecurityKey::RSA_1_5) {
                /*
                 * The RSA key formats are equal, so loading an RSA_1_5 key
                 * into an RSA_OAEP_MGF1P key can be done without problems.
                 * We therefore pretend that the input key is an
                 * RSA_OAEP_MGF1P key.
                 */
                $inputKeyAlgo = XMLSecurityKey::RSA_OAEP_MGF1P;
            }

            /* Make sure that the input key format is the same as the one used to encrypt the key. */
            if ($inputKeyAlgo !== $symKeyInfoAlgo) {
                throw new Exception(
                    'Algorithm mismatch between input key and key used to encrypt ' .
                    ' the symmetric key for the message. Key was: ' .
                    var_export($inputKeyAlgo, true) . '; message was: ' .
                    var_export($symKeyInfoAlgo, true)
                );
            }

            /** @var XMLSecEnc $encKey */
            $encKey = $symmetricKeyInfo->encryptedCtx;
            $symmetricKeyInfo->key = $inputKey->key;

            $keySize = $symmetricKey->getSymmetricKeySize();
            if ($keySize === null) {
                /* To protect against "key oracle" attacks, we need to be able to create a
                 * symmetric key, and for that we need to know the key size.
                 */
                throw new Exception(
                    'Unknown key size for encryption algorithm: ' . var_export($symmetricKey->type, true)
                );
            }

            try {
                /**
                 * @var string $key
                 * @psalm-suppress UndefinedClass
                 */
                $key = $encKey->decryptKey($symmetricKeyInfo);
                if (strlen($key) !== $keySize) {
                    throw new Exception(
                        'Unexpected key size (' . strval(strlen($key) * 8) . 'bits) for encryption algorithm: ' .
                        var_export($symmetricKey->type, true)
                    );
                }
            } catch (Exception $e) {
                /* We failed to decrypt this key. Log it, and substitute a "random" key. */
                self::getContainer()->getLogger()->error('Failed to decrypt symmetric key: ' . $e->getMessage());
                /* Create a replacement key, so that it looks like we fail in the same way as if the key was correctly
                 * padded. */

                /* We base the symmetric key on the encrypted key and private key, so that we always behave the
                 * same way for a given input key.
                 */
                $encryptedKey = $encKey->getCipherValue();
                if ($encryptedKey === null) {
                    throw new Exception('No CipherValue available in the encrypted element.');
                }

                /** @psalm-suppress PossiblyNullArgument */
                $pkey = openssl_pkey_get_details($symmetricKeyInfo->key);
                $pkey = sha1(serialize($pkey), true);
                $key = sha1($encryptedKey . $pkey, true);

                /* Make sure that the key has the correct length. */
                if (strlen($key) > $keySize) {
                    $key = substr($key, 0, $keySize);
                } elseif (strlen($key) < $keySize) {
                    $key = str_pad($key, $keySize);
                }
            }
            $symmetricKey->loadkey($key);
        } else {
            $symKeyAlgo = $symmetricKey->getAlgorithm();
            /* Make sure that the input key has the correct format. */
            if ($inputKeyAlgo !== $symKeyAlgo) {
                throw new Exception(
                    'Algorithm mismatch between input key and key in message. ' .
                    'Key was: ' . var_export($inputKeyAlgo, true) . '; message was: ' .
                    var_export($symKeyAlgo, true)
                );
            }
            $symmetricKey = $inputKey;
        }

        $algorithm = $symmetricKey->getAlgorithm();
        if (in_array($algorithm, $blacklist, true)) {
            throw new Exception('Algorithm disabled: ' . var_export($algorithm, true));
        }

        /**
         * @var string $decrypted
         * @psalm-suppress UndefinedClass
         */
        $decrypted = $enc->decryptNode($symmetricKey, false);

        /*
         * This is a workaround for the case where only a subset of the XML
         * tree was serialized for encryption. In that case, we may miss the
         * namespaces needed to parse the XML.
         */
        $xml = '<root xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ' .
                        'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
            $decrypted . '</root>';

        try {
            $newDoc = DOMDocumentFactory::fromString($xml);
        } catch (RuntimeException $e) {
            throw new Exception('Failed to parse decrypted XML. Maybe the wrong sharedkey was used?', 0, $e);
        }

        /** @psalm-suppress PossiblyNullPropertyFetch */
        $decryptedElement = $newDoc->firstChild->firstChild;
        if (!($decryptedElement instanceof DOMElement)) {
            throw new Exception('Missing decrypted element or it was not actually a DOMElement.');
        }

        return $decryptedElement;
    }


    /**
     * Decrypt an encrypted element.
     *
     * @param \DOMElement $encryptedData The encrypted data.
     * @param XMLSecurityKey $inputKey The decryption key.
     * @param array $blacklist Blacklisted decryption algorithms.
     * @throws \Exception
     * @return \DOMElement The decrypted element.
     */
    public static function decryptElement(
        DOMElement $encryptedData,
        XMLSecurityKey $inputKey,
        array $blacklist = []
    ): DOMElement {
        try {
            return self::doDecryptElement($encryptedData, $inputKey, $blacklist);
        } catch (Exception $e) {
            /*
             * Something went wrong during decryption, but for security
             * reasons we cannot tell the user what failed.
             */
            self::getContainer()->getLogger()->error('Decryption failed: ' . $e->getMessage());
            throw new Exception('Failed to decrypt XML element.', 0, $e);
        }
    }


    /**
     * Create a KeyDescriptor with the given certificate.
     *
     * @param string|null $x509Data The certificate, as a base64-encoded DER data.
     * @param string|null $keyName The name of the key as specified in the KeyInfo
     * @return \SAML2\XML\md\KeyDescriptor The keydescriptor.
     */
    public static function createKeyDescriptor(?string $x509Data = null, ?string $keyName = null): KeyDescriptor
    {
        if ($keyName === null && $x509Data === null) {
            throw new Exception('KeyDescriptor should contain either x509Data and/or keyName!');
        }

        $keyInfo = new KeyInfo();

        if ($keyName !== null) {
            $keynameEl = new KeyName();
            $keynameEl->setName($keyName);
            $keyInfo->addInfo($keynameEl);
        }

        if ($x509Data !== null) {
            $x509Certificate = new X509Certificate();
            $x509Certificate->setCertificate($x509Data);
            $x509Data = new X509Data();
            $x509Data->addData($x509Certificate);
            $keyInfo->addInfo($x509Data);
        }

        $keyDescriptor = new KeyDescriptor();
        $keyDescriptor->setKeyInfo($keyInfo);
        return $keyDescriptor;
    }


    /**
     * @return \SAML2\Compat\AbstractContainer
     */
    public static function getContainer(): AbstractContainer
    {
        return ContainerSingleton::getInstance();
    }
}
