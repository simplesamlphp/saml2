<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Class for handling SAML2 NameIDPolicy.
 *
 * @package simplesamlphp/saml2
 */
final class NameIDPolicy extends AbstractSamlpElement
{
    /** @var string|null */
    protected ?string $Format = null;

    /** @var string|null */
    protected ?string $SPNameQualifier = null;

    /** @var bool|null */
    protected ?bool $AllowCreate = null;


    /**
     * Initialize a NameIDPolicy.
     *
     * @param string|null $Format
     * @param string|null $SPNameQualifier
     * @param bool|null $AllowCreate
     */
    public function __construct(?string $Format = null, ?string $SPNameQualifier = null, ?bool $AllowCreate = null)
    {
        $this->setFormat($Format);
        $this->setSPNameQualifier($SPNameQualifier);
        $this->setAllowCreate($AllowCreate);
    }


    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->Format;
    }


    /**
     * @param string|null $Format
     */
    private function setFormat(?string $Format): void
    {
        Assert::nullOrNotWhitespaceOnly($Format);
        $this->Format = $Format;
    }


    /**
     * @return string|null
     */
    public function getSPNameQualifier(): ?string
    {
        return $this->SPNameQualifier;
    }


    /**
     * @param string|null $SPNameQualifier
     */
    private function setSPNameQualifier(?string $SPNameQualifier): void
    {
        Assert::nullOrNotWhitespaceOnly($SPNameQualifier);
        $this->SPNameQualifier = $SPNameQualifier;
    }


    /**
     * @return bool|null
     */
    public function getAllowCreate(): ?bool
    {
        return $this->AllowCreate;
    }


    /**
     * @param bool|null $AllowCreate
     */
    private function setAllowCreate(?bool $AllowCreate): void
    {
        $this->AllowCreate = $AllowCreate;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return (
            empty($this->Format)
            && empty($this->SPNameQualifier)
            && empty($this->AllowCreate)
        );
    }


    /**
     * Convert XML into a NameIDPolicy
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\samlp\NameIDPolicy
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'NameIDPolicy', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, NameIDPolicy::NS, InvalidDOMElementException::class);

        $Format = self::getAttribute($xml, 'Format', null);
        $SPNameQualifier = self::getAttribute($xml, 'SPNameQualifier', null);
        $AllowCreate = self::getAttribute($xml, 'AllowCreate', null);

        return new self(
            $Format,
            $SPNameQualifier,
            ($AllowCreate === 'true') ? true : false
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

        if (isset($this->Format)) {
            $e->setAttribute('Format', $this->Format);
        }

        if (isset($this->SPNameQualifier)) {
            $e->setAttribute('SPNameQualifier', $this->SPNameQualifier);
        }

        if (isset($this->AllowCreate)) {
            $e->setAttribute('AllowCreate', var_export($this->AllowCreate, true));
        }

        return $e;
    }
}
