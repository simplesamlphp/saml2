<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMElement;
use DOMNode;
use Exception;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;

use function count;

/**
 * Class handling encrypted assertions.
 *
 * @package SimpleSAMLphp
 */
class EncryptedAssertion
{
    /**
     * The current encrypted assertion.
     *
     * @var \DOMElement
     */
    private DOMElement $encryptedData;


    /**
     * @var bool
     */
    protected bool $wasSignedAtConstruction = false;

    /**
     * Constructor for SAML 2 encrypted assertions.
     *
     * @param \DOMElement|null $xml The encrypted assertion XML element.
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $xpCache = XPath::getXPath($xml);
        /** @var \DOMElement[] $data */
        $data = XPath::xpQuery($xml, './xenc:EncryptedData', $xpCache);
        if (empty($data)) {
            throw new MissingElementException('Missing encrypted data in <saml:EncryptedAssertion>.');
        } elseif (count($data) > 1) {
            throw new TooManyElementsException('More than one encrypted data element in <saml:EncryptedAssertion>.');
        }
        $this->encryptedData = $data[0];
    }


    /**
     * @return bool
     */
    public function wasSignedAtConstruction(): bool
    {
        return $this->wasSignedAtConstruction;
    }

    /**
     * Set the assertion.
     *
     * @param \SimpleSAML\SAML2\Assertion $assertion The assertion.
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey  $key       The key we should use to encrypt the assertion.
     * @throws \Exception
     * @return void
     */
    public function setAssertion(Assertion $assertion, XMLSecurityKey $key): void
    {
        $xml = $assertion->toXML();

        Utils::getContainer()->debugMessage($xml, 'encrypt');

        $enc = new XMLSecEnc();
        $enc->setNode($xml);
        $enc->type = Constants::XMLENC_ELEMENT;

        switch ($key->type) {
            case Constants::BLOCK_ENC_3DES:
            case Constants::BLOCK_ENC_AES128:
            case Constants::BLOCK_ENC_AES192:
            case Constants::BLOCK_ENC_AES256:
            case Constants::BLOCK_ENC_AES128_GCM:
            case Constants::BLOCK_ENC_AES192_GCM:
            case Constants::BLOCK_ENC_AES256_GCM:
                $symmetricKey = $key;
                break;

            case Constants::KEY_TRANSPORT_RSA_1_5:
            case Constants::KEY_TRANSPORT_OAEP_MGF1P:
                $symmetricKey = new XMLSecurityKey(Constants::BLOCK_ENC_AES128);
                $symmetricKey->generateSessionKey();

                $enc->encryptKey($key, $symmetricKey);

                break;

            default:
                throw new Exception('Unknown key type for encryption: ' . $key->type);
        }

        /**
         * @psalm-suppress UndefinedClass
         */
        $this->encryptedData = $enc->encryptNode($symmetricKey);
    }


    /**
     * Retrieve the assertion.
     *
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey $inputKey  The key we should use to decrypt the assertion.
     * @param array $blacklist Blacklisted decryption algorithms.
     * @return \SimpleSAML\SAML2\Assertion The decrypted assertion.
     */
    public function getAssertion(XMLSecurityKey $inputKey, array $blacklist = []): Assertion
    {
        $assertionXML = Utils::decryptElement($this->encryptedData, $inputKey, $blacklist);

        Utils::getContainer()->debugMessage($assertionXML, 'decrypt');

        return new Assertion($assertionXML);
    }


    /**
     * Convert this encrypted assertion to an XML element.
     *
     * @param  \DOMNode|null $parentElement The DOM node the assertion should be created in.
     * @return \DOMElement   This encrypted assertion.
     */
    public function toXML(DOMNode $parentElement = null): DOMElement
    {
        if ($parentElement === null) {
            $document = DOMDocumentFactory::create();
            $parentElement = $document;
        } else {
            $document = $parentElement->ownerDocument;
        }

        $root = $document->createElementNS(Constants::NS_SAML, 'saml:' . 'EncryptedAssertion');
        $parentElement->appendChild($root);

        $root->appendChild($document->importNode($this->encryptedData, true));

        return $root;
    }
}
