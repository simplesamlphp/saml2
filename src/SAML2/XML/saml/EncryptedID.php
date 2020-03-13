<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\XML\xenc\EncryptedData;
use SAML2\XML\xenc\EncryptedKey;
use Webmozart\Assert\Assert;

/**
 * Class representing an encrypted identifier.
 *
 * @package simplesamlphp/saml2
 */
class EncryptedID extends AbstractSamlElement
{

    /**
     * The current encrypted ID.
     *
     * @var \SAML2\XML\xenc\EncryptedData
     */
    protected $encryptedData;

    /**
     * A list of encrypted keys.
     *
     * @var \SAML2\XML\xenc\EncryptedKey[]
     */
    protected $encryptedKeys = [];


    /**
     * EncryptedID constructor.
     *
     * @param \SAML2\XML\xenc\EncryptedData $encryptedData The EncryptedData object.
     * @param \SAML2\XML\xenc\EncryptedKey[] $encryptedKeys An array of zero or more EncryptedKey objects.
     */
    public function __construct(EncryptedData $encryptedData, array $encryptedKeys)
    {
        $this->setEncryptedData($encryptedData);
        $this->setEncryptedKeys($encryptedKeys);
    }


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
            'All encrypted keys in <saml:EncryptedID> must be an instance of EncryptedKey.'
        );
        $this->encryptedKeys = $encryptedKeys;
    }


    /**
     * @inheritDoc
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'EncryptedID');
        Assert::same($xml->namespaceURI, EncryptedID::NS);

        $ed = EncryptedData::getChildrenOfClass($xml);
        Assert::count($ed, 1, 'No more or less than one EncryptedData element allowed in <saml:EncryptedID>.');

        $ek = EncryptedKey::getChildrenOfClass($xml);

        return new self($ed[0], $ek);
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
