<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\TypedTextContentTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;

use function strval;

/**
 * SAML NameIDType abstract data type.
 *
 * @package simplesamlphp/saml2
 */

abstract class NameIDType extends AbstractSamlElement implements IdentifierInterface
{
    use IDNameQualifiersTrait;
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = SAMLStringValue::class;


    /**
     * Initialize a saml:NameIDType from scratch
     *
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue $value
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $Format
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $SPProvidedID
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $NameQualifier
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $SPNameQualifier
     */
    protected function __construct(
        SAMLStringValue|SAMLAnyURIValue $value,
        protected ?SAMLStringValue $NameQualifier = null,
        protected ?SAMLStringValue $SPNameQualifier = null,
        protected ?SAMLAnyURIValue $Format = null,
        protected ?SAMLStringValue $SPProvidedID = null,
    ) {
        $this->setContent($value);
    }


    /**
     * Collect the value of the Format-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null
     */
    public function getFormat(): ?SAMLAnyURIValue
    {
        return $this->Format;
    }


    /**
     * Collect the value of the SPProvidedID-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getSPProvidedID(): ?SAMLStringValue
    {
        return $this->SPProvidedID;
    }


    /**
     * Convert XML into an NameID
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, static::getLocalName(), InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, static::NS, InvalidDOMElementException::class);

        return new static(
            SAMLStringValue::fromString($xml->textContent),
            self::getOptionalAttribute($xml, 'NameQualifier', SAMLStringValue::class, null),
            self::getOptionalAttribute($xml, 'SPNameQualifier', SAMLStringValue::class, null),
            self::getOptionalAttribute($xml, 'Format', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'SPProvidedID', SAMLStringValue::class, null),
        );
    }


    /**
     * Convert this NameIDType to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = strval($this->getContent());

        if ($this->getNameQualifier() !== null) {
            $e->setAttribute('NameQualifier', $this->getNameQualifier()->getValue());
        }

        if ($this->getSPNameQualifier() !== null) {
            $e->setAttribute('SPNameQualifier', $this->getSPNameQualifier()->getValue());
        }

        if ($this->getFormat() !== null) {
            $e->setAttribute('Format', $this->getFormat()->getValue());
        }

        if ($this->getSPProvidedID() !== null) {
            $e->setAttribute('SPProvidedID', $this->getSPProvidedID()->getValue());
        }

        return $e;
    }
}
