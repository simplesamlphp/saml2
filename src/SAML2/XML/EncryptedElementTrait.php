<?php

declare(strict_types=1);

namespace SAML2\XML;

use DOMElement;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Utils;
use SAML2\XML\xenc\EncryptedData;
use SAML2\XML\xenc\EncryptedKey;
use SimpleSAML\Assert\Assert;

/**
 * Trait aggregating functionality for encrypted elements.
 *
 * @package simplesamlphp/saml2
 */
trait EncryptedElementTrait
{
    /**
     * The current encrypted ID.
     *
     * @var \SAML2\XML\xenc\EncryptedData
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $encryptedData;

    /**
     * A list of encrypted keys.
     *
     * @var \SAML2\XML\xenc\EncryptedKey[]
     */
    protected $encryptedKeys = [];


    /**
     * Constructor for encrypted elements.
     *
     * @param \SAML2\XML\xenc\EncryptedData $encryptedData The EncryptedData object.
     * @param \SAML2\XML\xenc\EncryptedKey[] $encryptedKeys An array of zero or more EncryptedKey objects.
     */
    public function __construct(EncryptedData $encryptedData, array $encryptedKeys)
    {
        $this->setEncryptedData($encryptedData);
        $this->setEncryptedKeys($encryptedKeys);
    }


    abstract public function instantiateParentElement(DOMElement $parent = null): DOMElement;


    abstract public function getQualifiedName(): string;


    /**
     * Get the EncryptedData object.
     *
     * @return \SAML2\XML\xenc\EncryptedData
     */
    public function getEncryptedData(): EncryptedData
    {
        return $this->encryptedData;
    }


    /**
     * @param \SAML2\XML\xenc\EncryptedData $encryptedData
     */
    protected function setEncryptedData(EncryptedData $encryptedData): void
    {
        $this->encryptedData = $encryptedData;
    }


    /**
     * Get the array of EncryptedKey objects
     *
     * @return \SAML2\XML\xenc\EncryptedKey[]
     */
    public function getEncryptedKeys(): array
    {
        return $this->encryptedKeys;
    }


    /**
     * @param \SAML2\XML\xenc\EncryptedKey[] $encryptedKeys
     */
    protected function setEncryptedKeys(array $encryptedKeys): void
    {
        Assert::allIsInstanceOf(
            $encryptedKeys,
            EncryptedKey::class,
            'All encrypted keys in <' . $this->getQualifiedName() . '> must be an instance of EncryptedKey.'
        );

        $this->encryptedKeys = $encryptedKeys;
    }


    /**
     * Create an encrypted element from a given unencrypted element and a key.
     *
     * @param \SAML2\XML\AbstractXMLElement $element
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey $key
     *
     * @return \SAML2\XML\EncryptedElementInterface
     * @throws \Exception
     */
    public static function fromUnencryptedElement(
        AbstractXMLElement $element,
        XMLSecurityKey $key
    ): EncryptedElementInterface {
        $xml = $element->toXML();

        Utils::getContainer()->debugMessage($xml, 'encrypt');

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
            case XMLSecurityKey::RSA_SHA1:
            case XMLSecurityKey::RSA_SHA256:
            case XMLSecurityKey::RSA_SHA384:
            case XMLSecurityKey::RSA_SHA512:
            case XMLSecurityKey::RSA_OAEP_MGF1P:
                $symmetricKey = new XMLSecurityKey(XMLSecurityKey::AES128_CBC);
                $symmetricKey->generateSessionKey();

                $enc->encryptKey($key, $symmetricKey);

                break;

            default:
                throw new \Exception('Unknown key type for encryption: ' . $key->type);
        }

        $dom = $enc->encryptNode($symmetricKey);
        /** @var \SAML2\XML\xenc\EncryptedData $encData */
        $encData = EncryptedData::fromXML($dom);
        return new static($encData, []);
    }


    /**
     * @inheritDoc
     * @return \SAML2\XML\AbstractXMLElement
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, AbstractXMLElement::getClassName(static::class), InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, static::NS, InvalidDOMElementException::class);

        $ed = EncryptedData::getChildrenOfClass($xml);
        Assert::count($ed, 1, 'No more or less than one EncryptedData element allowed in ' .
            AbstractXMLElement::getClassName(static::class) . '.');

        $ek = EncryptedKey::getChildrenOfClass($xml);

        return new static($ed[0], $ek);
    }


    /**
     * @inheritDoc
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $this->encryptedData->toXML($e);

        foreach ($this->encryptedKeys as $key) {
            $key->toXML($e);
        }

        return $e;
    }
}
