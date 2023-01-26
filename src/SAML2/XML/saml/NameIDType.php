<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\XML\StringElementTrait;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;

/**
 * SAML NameIDType abstract data type.
 *
 * @package simplesamlphp/saml2
 */

abstract class NameIDType extends AbstractBaseIDType
{
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
        ?string $nameQualifier = null,
        ?string $spNameQualifier = null,
        protected ?string $format = null,
        protected ?string $spProvidedID = null,
    ) {
        Assert::nullOrValidURI($format); // Covers the empty string
        Assert::nullOrNotWhitespaceOnly($spProvidedID);

        parent::__construct($nameQualifier, $spNameQualifier);

        $this->setContent($value);
    }


    /**
     * Collect the value of the Format-property
     *
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }


    /**
     * Collect the value of the SPProvidedID-property
     *
     * @return string|null
     */
    public function getSPProvidedID(): ?string
    {
        return $this->spProvidedID;
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
     * Convert this NameIDType to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this NameIDType.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

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
