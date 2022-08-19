<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;

/**
 * Class representing a SAML2 AttributeStatement
 *
 * @package simplesamlphp/saml2
 */
class AttributeStatement extends AbstractStatement
{
    /** @var \SimpleSAML\SAML2\XML\saml\Attribute[] */
    protected array $attributes = [];

    /** @var \SimpleSAML\SAML2\XML\saml\EncryptedAttribute[] */
    protected array $encryptedAttributes = [];


    /**
     * AttributeStatement constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[] $attributes
     * @param \SimpleSAML\SAML2\XML\saml\EncryptedAttribute[] $encryptedAttributes
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
     * @return \SimpleSAML\SAML2\XML\saml\Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[] $attributes
     */
    private function setAttributes(array $attributes): void
    {
        Assert::allIsInstanceOf($attributes, Attribute::class);
        $this->attributes = $attributes;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\EncryptedAttribute[]
     */
    public function getEncryptedAttributes(): array
    {
        return $this->encryptedAttributes;
    }


    /**
     * @param \SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface
     * @param array $blacklist
     *
     * @throws \Exception
     */
    public function decryptAttributes(EncryptionAlgorithmInterface $decryptor, array $blacklist = []): void
    {
        Assert::allStringNotEmpty($blacklist);

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
     * @param \SimpleSAML\SAML2\XML\saml\EncryptedAttribute[] $encryptedAttributes
     */
    private function setEncryptedAttributes(array $encryptedAttributes): void
    {
        Assert::allIsInstanceOf($encryptedAttributes, EncryptedAttribute::class);
        $this->encryptedAttributes = $encryptedAttributes;
    }


    /**
     * @param \DOMElement $xml
     * @return \SimpleSAML\SAML2\XML\saml\AttributeStatement
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AttributeStatement', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeStatement::NS, InvalidDOMElementException::class);

        return new self(
            Attribute::getChildrenOfClass($xml),
            EncryptedAttribute::getChildrenOfClass($xml)
        );
    }


    /**
     * Convert this Attribute to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Attribute to.
     * @return \DOMElement
     */
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
