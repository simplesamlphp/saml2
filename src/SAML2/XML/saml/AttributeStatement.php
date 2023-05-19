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
class AttributeStatement extends AbstractStatementType
{
    /**
     * AttributeStatement constructor.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[] $attributes
     * @param \SimpleSAML\SAML2\XML\saml\EncryptedAttribute[] $encryptedAttributes
     */
    public function __construct(
        protected array $attributes = [],
        protected array $encryptedAttributes = [],
    ) {
        Assert::true(!empty($attributes) || !empty($encryptedAttributes));
        Assert::allIsInstanceOf($attributes, Attribute::class);
        Assert::allIsInstanceOf($encryptedAttributes, EncryptedAttribute::class);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\EncryptedAttribute[]
     */
    public function getEncryptedAttributes(): array
    {
        return $this->encryptedAttributes;
    }


    /**
     * @return bool
     */
    public function hasEncryptedAttributes(): bool
    {
        return !empty($this->encryptedAttributes);
    }


    /**
     * @param \DOMElement $xml
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AttributeStatement', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeStatement::NS, InvalidDOMElementException::class);

        return new static(
            Attribute::getChildrenOfClass($xml),
            EncryptedAttribute::getChildrenOfClass($xml),
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

        foreach ($this->getAttributes() as $attribute) {
            $attribute->toXML($e);
        }

        foreach ($this->getEncryptedAttributes() as $encryptedAttribute) {
            $encryptedAttribute->toXML($e);
        }

        return $e;
    }
}
