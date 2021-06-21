<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\IDNameQualifiersTrait;
use SimpleSAML\XML\XMLStringElementTrait;

/**
 * SAML NameIDType abstract data type.
 *
 * @package simplesamlphp/saml2
 */

abstract class NameIDType extends AbstractSamlElement implements IdentifierInterface
{
    use IDNameQualifiersTrait;
    use XMLStringElementTrait;

    /**
     * A URI reference representing the classification of string-based identifier information. See Section 8.3 for the
     * SAML-defined URI references that MAY be used as the value of the Format attribute and their associated
     * descriptions and processing rules. Unless otherwise specified by an element based on this type, if no Format
     * value is provided, then the value urn:oasis:names:tc:SAML:1.0:nameid-format:unspecified (see Section 8.3.1) is in
     * effect.
     *
     * When a Format value other than one specified in Section 8.3 is used, the content of an element of this type is to
     * be interpreted according to the definition of that format as provided outside of this specification. If not
     * otherwise indicated by the definition of the format, issues of anonymity, pseudonymity, and the persistence of
     * the identifier with respect to the asserting and relying parties are implementation-specific.
     *
     * @var string|null
     *
     * @see saml-core-2.0-os
     */
    protected ?string $Format = null;

    /**
     * A name identifier established by a service provider or affiliation of providers for the entity, if different from
     * the primary name identifier given in the content of the element. This attribute provides a means of integrating
     * the use of SAML with existing identifiers already in use by a service provider. For example, an existing
     * identifier can be "attached" to the entity using the Name Identifier Management protocol defined in Section 3.6.
     *
     * @var string|null
     *
     * @see saml-core-2.0-os
     */
    protected ?string $SPProvidedID = null;


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
        ?string $NameQualifier = null,
        ?string $SPNameQualifier = null,
        ?string $Format = null,
        ?string $SPProvidedID = null
    ) {
        $this->setContent($value);
        $this->setNameQualifier($NameQualifier);
        $this->setSPNameQualifier($SPNameQualifier);
        $this->setFormat($Format);
        $this->setSPProvidedID($SPProvidedID);
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
     * Set the value of the Format-property
     *
     * @param string|null $format
     */
    private function setFormat(?string $format): void
    {
        Assert::nullOrNotWhitespaceOnly($format);
        $this->Format = $format;
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
     * Set the value of the SPProvidedID-property
     *
     * @param string|null $spProvidedID
     */
    public function setSPProvidedID(?string $spProvidedID): void
    {
        Assert::nullOrNotWhitespaceOnly($spProvidedID);
        $this->SPProvidedID = $spProvidedID;
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
        /** @psalm-var \DOMDocument $element->ownerDocument */
        $element = $this->instantiateParentElement($parent);

        if ($this->NameQualifier !== null) {
            $element->setAttribute('NameQualifier', $this->NameQualifier);
        }

        if ($this->SPNameQualifier !== null) {
            $element->setAttribute('SPNameQualifier', $this->SPNameQualifier);
        }

        if ($this->Format !== null) {
            $element->setAttribute('Format', $this->Format);
        }

        if ($this->SPProvidedID !== null) {
            $element->setAttribute('SPProvidedID', $this->SPProvidedID);
        }

        $element->textContent = $this->content;
        return $element;
    }
}
