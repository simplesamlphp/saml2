<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use Webmozart\Assert\Assert;

/**
 * SAML NameIDType abstract data type.
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */

abstract class NameIDType extends BaseIDType
{
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
    protected $Format = null;

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
    protected $SPProvidedID = null;

    /**
     * The NameIDType complex type is used when an element serves to represent an entity by a string-valued name.
     *
     * @var string
     */
    protected $value;


    /**
     * Initialize a saml:NameIDType from scratch
     *
     * @param string $value
     * @param string|null $Format
     * @param string|null $SPProvidedID
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    public function __construct(
        string $value,
        ?string $Format = null,
        ?string $SPProvidedID = null,
        ?string $NameQualifier = null,
        ?string $SPNameQualifier = null
    ) {
        parent::__construct($NameQualifier, $SPNameQualifier);

        $this->setFormat($Format);
        $this->setSPProvidedID($SPProvidedID);
        $this->setValue($value);
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
     * @return void
     */
    private function setFormat(?string $format): void
    {
        Assert::nullOrNotWhitespaceOnly($format);
        $this->Format = $format;
    }


    /**
     * Collect the value of the value-property
     *
     * @return string
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getValue(): string
    {
        return $this->value;
    }


    /**
     * Set the value of the value-property
     * @param string $value
     *
     * @return void
     */
    private function setValue(string $value): void
    {
        Assert::notWhitespaceOnly($value);
        $this->value = trim($value);
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
     * @return void
     */
    private function setSPProvidedID(?string $spProvidedID): void
    {
        $this->SPProvidedID = $spProvidedID;
    }


    /**
     * Convert this NameIDType to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this NameIDType.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $element = parent::toXML($parent);

        if ($this->Format !== null) {
            $element->setAttribute('Format', $this->Format);
        }

        if ($this->SPProvidedID !== null) {
            $element->setAttribute('SPProvidedID', $this->SPProvidedID);
        }

        $value = $element->ownerDocument->createTextNode($this->value);
        $element->appendChild($value);

        return $element;
    }
}
