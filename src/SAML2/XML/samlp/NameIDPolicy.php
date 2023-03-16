<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function var_export;

/**
 * Class for handling SAML2 NameIDPolicy.
 *
 * @package simplesamlphp/saml2
 */
final class NameIDPolicy extends AbstractSamlpElement
{
    /**
     * Initialize a NameIDPolicy.
     *
     * @param string|null $Format
     * @param string|null $SPNameQualifier
     * @param bool|null $AllowCreate
     */
    public function __construct(
        protected ?string $Format = null,
        protected ?string $SPNameQualifier = null,
        protected ?bool $AllowCreate = null,
    ) {
        Assert::nullOrValidURI($Format); // Covers the empty string
        Assert::nullOrNotWhitespaceOnly($SPNameQualifier);
    }


    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->Format;
    }


    /**
     * @return string|null
     */
    public function getSPNameQualifier(): ?string
    {
        return $this->SPNameQualifier;
    }


    /**
     * @return bool|null
     */
    public function getAllowCreate(): ?bool
    {
        return $this->AllowCreate;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return empty($this->Format)
            && empty($this->SPNameQualifier)
            && empty($this->AllowCreate);
    }


    /**
     * Convert XML into a NameIDPolicy
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'NameIDPolicy', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, NameIDPolicy::NS, InvalidDOMElementException::class);

        $Format = self::getAttribute($xml, 'Format', null);
        $SPNameQualifier = self::getAttribute($xml, 'SPNameQualifier', null);
        $AllowCreate = self::getAttribute($xml, 'AllowCreate', null);

        return new static(
            $Format,
            $SPNameQualifier,
            ($AllowCreate === 'true') ? true : false,
        );
    }


    /**
     * Convert this NameIDPolicy to XML.
     *
     * @param \DOMElement|null $parent The element we should append this NameIDPolicy to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getFormat()) {
            $e->setAttribute('Format', $this->getFormat());
        }

        if ($this->getSPNameQualifier()) {
            $e->setAttribute('SPNameQualifier', $this->getSPNameQualifier());
        }

        if ($this->getAllowCreate() !== null) {
            $e->setAttribute('AllowCreate', var_export($this->getAllowCreate(), true));
        }

        return $e;
    }
}
