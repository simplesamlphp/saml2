<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\StringElementTrait;

/**
 * SAML NameIDType abstract data type.
 *
 * @package simplesamlphp/saml2
 */

abstract class NameIDType extends AbstractSamlElement implements IdentifierInterface
{
    use IDNameQualifiersTrait;
    use StringElementTrait;


    /**
     * Initialize a saml:NameIDType from scratch
     *
     * @param string $value
     * @param string|null $Format
     * @param string|null $SPProvidedID
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    protected function __construct(
        string $value,
        protected ?string $NameQualifier = null,
        protected ?string $SPNameQualifier = null,
        protected ?string $Format = null,
        protected ?string $SPProvidedID = null,
    ) {
        Assert::nullOrNotWhitespaceOnly($NameQualifier);
        Assert::nullOrNotWhitespaceOnly($SPNameQualifier);
        SAMLAssert::nullOrValidURI($Format);
        Assert::nullOrNotWhitespaceOnly($SPProvidedID);

        $this->setContent($value);
    }


    /**
     * Collect the value of the Format-property
     *
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->Format;
    }


    /**
     * Collect the value of the SPProvidedID-property
     *
     * @return string|null
     */
    public function getSPProvidedID(): ?string
    {
        return $this->SPProvidedID;
    }


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(string $content): void
    {
        Assert::notWhitespaceOnly($content);
    }


    /**
     * Convert XML into an NameID
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, static::getLocalName(), InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, static::NS, InvalidDOMElementException::class);

        $NameQualifier = self::getOptionalAttribute($xml, 'NameQualifier', null);
        $SPNameQualifier = self::getOptionalAttribute($xml, 'SPNameQualifier', null);
        $Format = self::getOptionalAttribute($xml, 'Format', null);
        $SPProvidedID = self::getOptionalAttribute($xml, 'SPProvidedID', null);

        return new static($xml->textContent, $NameQualifier, $SPNameQualifier, $Format, $SPProvidedID);
    }


    /**
     * Convert this NameIDType to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this NameIDType.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getNameQualifier() !== null) {
            $e->setAttribute('NameQualifier', $this->getNameQualifier());
        }

        if ($this->getSPNameQualifier() !== null) {
            $e->setAttribute('SPNameQualifier', $this->getSPNameQualifier());
        }

        if ($this->getFormat() !== null) {
            $e->setAttribute('Format', $this->getFormat());
        }

        if ($this->getSPProvidedID() !== null) {
            $e->setAttribute('SPProvidedID', $this->getSPProvidedID());
        }

        $e->textContent = $this->getContent();
        return $e;
    }
}
