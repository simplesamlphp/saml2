<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Webmozart\Assert\Assert;

/**
 * Class representing a SAML2 AttributeStatement
 *
 * @package simplesamlphp/saml2
 */
class AttributeStatement extends AbstractStatement
{
    /** @var \SAML2\XML\saml\Attribute[] */
    protected $attributes = [];

    /** @var \SAML2\XML\saml\EncryptedAttribute[] */
    protected $encryptedAttributes = [];


    /**
     * AttributeStatement constructor.
     *
     * @param \SAML2\XML\saml\Attribute[]|null $attributes
     * @param \SAML2\XML\saml\EncryptedAttribute[]|null $encryptedAttributes
     */
    public function __construct(
        array $attributes = [],
        array $encryptedAttributes = []
    ) {
        Assert::true(!empty($attributes) || !empty($encryptedAttributes));
        $this->setAttributes($attributes);
        $this->setEncryptedAttributes($encryptedAttributes);
    }


    /**
     * @return \SAML2\XML\saml\Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }


    /**
     * @param \SAML2\XML\saml\Attribute[] $attributes
     */
    private function setAttributes(array $attributes): void
    {
        Assert::allIsInstanceOf($attributes, Attribute::class);
        $this->attributes = $attributes;
    }


    /**
     * @return \SAML2\XML\saml\EncryptedAttribute[]
     */
    public function getEncryptedAttributes(): array
    {
        return $this->encryptedAttributes;
    }


    /**
     * @param XMLSecurityKey $key
     * @param array $blacklist
     *
     * @throws \Exception
     */
    public function decryptAttributes(XMLSecurityKey $key, array $blacklist = []): void
    {
        foreach ($this->encryptedAttributes as $encryptedAttribute) {
            $this->attributes[] = $encryptedAttribute->decrypt($key, $blacklist);
        }
    }


    /**
     * @return bool
     */
    public function hasEncryptedAttributes(): bool
    {
        return !empty($this->encryptedAttributes);
    }


    /**
     * @param \SAML2\XML\saml\EncryptedAttribute[] $encryptedAttributes
     */
    private function setEncryptedAttributes(array $encryptedAttributes): void
    {
        Assert::allIsInstanceOf($encryptedAttributes, EncryptedAttribute::class);
        $this->encryptedAttributes = $encryptedAttributes;
    }


    /**
     * @param DOMElement $xml
     *
     * @return object
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AttributeStatement');
        Assert::same($xml->namespaceURI, AttributeStatement::NS);

        return new self(
            Attribute::getChildrenOfClass($xml),
            EncryptedAttribute::getChildrenOfClass($xml)
        );
    }


    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->attributes as $attribute) {
            $attribute->toXML($e);
        }

        foreach ($this->encryptedAttributes as $encryptedAttribute) {
            $encryptedAttribute->toXML($e);
        }

        return $e;
    }
}
