<?php

namespace SAML2\XML;

use DOMElement;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\DOMDocumentFactory;
use SAML2\Exception\RuntimeException;
use SAML2\Utils;

/**
 * SAML EncryptedElementType class.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */

abstract class EncryptedElementType extends AbstractXMLElement implements EncryptedElementInterface
{
    /**
     * The encrypted element.
     *
     * @var \DOMElement
     */
    protected $encryptedData;

    /**
     * The keys to be used for decryption.
     *
     * @var \RobRichards\XMLSecLibs\XMLSecurityKey[]
     */
    protected $decryptionKeys = [];

    /**
     * The key to be used for encryption.
     *
     * @var \RobRichards\XMLSecLibs\XMLSecurityKey
     */
    protected $encryptionKey;


    /**
     * @param \DOMElement $xml
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey $key
     */
    public function encryptElement(DOMElement $xml, XMLSecurityKey $key): void
    {
        $enc = new XMLSecEnc();
        $enc->setNode($xml);
        $enc->type = XMLSecEnc::Element;

        switch ($key->type) {
            case XMLSecurityKey::TRIPLEDES_CBC:
            case XMLSecurityKey::AES128_CBC:
            case XMLSecurityKey::AES192_CBC:
            case XMLSecurityKey::AES256_CBC:
                $symmetricKey = $key;
                break;

            case XMLSecurityKey::RSA_1_5:
            case XMLSecurityKey::RSA_OAEP_MGF1P:
                $symmetricKey = new XMLSecurityKey(XMLSecurityKey::AES128_CBC);
                $symmetricKey->generateSessionKey();

                $enc->encryptKey($key, $symmetricKey);

                break;

            default:
                throw new \Exception('Unknown key type for encryption: ' . $key->type);
        }

        $this->encryptedData = $enc->encryptNode($symmetricKey);
    }


    /**
     * @param \DOMElement $encryptedData
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey $key
     * @param string[] $blacklist
     * @return \DOMElement
     */
    public static function decryptElement(DOMElement $encryptedData, XMLSecurityKey $key, array $blacklist = []): DOMElement
    {
        try {
            return self::doDecryptElement($encryptedData, $key, $blacklist);
        } catch (\Exception $e) {
            /*
             * Something went wrong during decryption, but for security
             * reasons we cannot tell the user what failed.
             */
            Utils::getContainer()->getLogger()->error('Decryption failed: ' . $e->getMessage());
            throw new \Exception('Failed to decrypt XML element.', 0, $e);
        }
    }


    /**
     * Decrypt an encrypted element.
     *
     * This is an internal helper function.
     *
     * @param \DOMElement $encryptedData The encrypted data.
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey $key The decryption key.
     * @param string[] &$blacklist Blacklisted decryption algorithms.
     * @throws \Exception
     * @return \DOMElement The decrypted element.
     */
    private static function doDecryptElement(
        DOMElement $encryptedData,
        XMLSecurityKey $key,
        array &$blacklist
    ): DOMElement {
        $enc = new XMLSecEnc();

        $enc->setNode($encryptedData);
        $enc->type = $encryptedData->getAttribute("Type");

        $symmetricKey = $enc->locateKey($encryptedData);
        if (!$symmetricKey) {
            throw new \Exception('Could not locate key algorithm in encrypted data.');
        }

        $symmetricKeyInfo = $enc->locateKeyInfo($symmetricKey);
        if (!$symmetricKeyInfo) {
            throw new \Exception('Could not locate <dsig:KeyInfo> for the encrypted key.');
        }

        $inputKeyAlgo = $key->getAlgorithm();
        if ($symmetricKeyInfo->isEncrypted) {
            $symKeyInfoAlgo = $symmetricKeyInfo->getAlgorithm();

            if (in_array($symKeyInfoAlgo, $blacklist, true)) {
                throw new \Exception('Algorithm disabled: ' . var_export($symKeyInfoAlgo, true));
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
                throw new \Exception(
                    'Algorithm mismatch between input key and key used to encrypt ' .
                    ' the symmetric key for the message. Key was: ' .
                    var_export($inputKeyAlgo, true) . '; message was: ' .
                    var_export($symKeyInfoAlgo, true)
                );
            }

            /** @var XMLSecEnc $encKey */
            $encKey = $symmetricKeyInfo->encryptedCtx;
            $symmetricKeyInfo->key = $key->key;

            $keySize = $symmetricKey->getSymmetricKeySize();
            if ($keySize === null) {
                /* To protect against "key oracle" attacks, we need to be able to create a
                 * symmetric key, and for that we need to know the key size.
                 */
                throw new \Exception(
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
                    throw new \Exception(
                        'Unexpected key size (' . strval(strlen($key) * 8) . 'bits) for encryption algorithm: ' .
                        var_export($symmetricKey->type, true)
                    );
                }
            } catch (\Exception $e) {
                /* We failed to decrypt this key. Log it, and substitute a "random" key. */
                Utils::getContainer()->getLogger()->error('Failed to decrypt symmetric key: ' . $e->getMessage());
                /* Create a replacement key, so that it looks like we fail in the same way as if the key was correctly
                 * padded. */

                /* We base the symmetric key on the encrypted key and private key, so that we always behave the
                 * same way for a given input key.
                 */
                $encryptedKey = $encKey->getCipherValue();
                if ($encryptedKey === null) {
                    throw new \Exception('No CipherValue available in the encrypted element.');
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
                throw new \Exception(
                    'Algorithm mismatch between input key and key in message. ' .
                    'Key was: ' . var_export($inputKeyAlgo, true) . '; message was: ' .
                    var_export($symKeyAlgo, true)
                );
            }
            $symmetricKey = $key;
        }

        $algorithm = $symmetricKey->getAlgorithm();
        if (in_array($algorithm, $blacklist, true)) {
            throw new \Exception('Algorithm disabled: ' . var_export($algorithm, true));
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
            throw new \Exception('Failed to parse decrypted XML. Maybe the wrong sharedkey was used?', 0, $e);
        }

        /** @psalm-suppress PossiblyNullPropertyFetch */
        $decryptedElement = $newDoc->firstChild->firstChild;
        if (!($decryptedElement instanceof DOMElement)) {
            throw new \Exception('Missing decrypted element or it was not actually a DOMElement.');
        }

        return $decryptedElement;
    }
}
