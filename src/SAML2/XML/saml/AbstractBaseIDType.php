<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\IDNameQualifiersTrait;
use Webmozart\Assert\Assert;

/**
 * SAML BaseIDType abstract data type.
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
abstract class AbstractBaseIDType extends AbstractSamlElement implements IdentifierInterface
{
    use IDNameQualifiersTrait;

    /** @var string|null */
    protected $value;


    /**
     * Initialize a saml:BaseIDType from scratch
     *
     * @param string|null $value
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    protected function __construct(
        ?string $value = null,
        ?string $NameQualifier = null,
        ?string $SPNameQualifier = null
    ) {
        $this->setValue($value);
        $this->setNameQualifier($NameQualifier);
        $this->setSPNameQualifier($SPNameQualifier);
    }


    public function getValue(): string
    {
        return $this->value;
    }


    protected function setValue(string $value): void
    {
        $this->value = $value;
    }


    /**
     * Get the XML local name of the element represented by this class.
     *
     * @return string
     */
    public function getLocalName(): string
    {
        // All descendants of this class are supposed to be <saml:BaseID /> elements and shouldn't define a new element
        return 'BaseID';
    }


    /**
     * Convert this NameIDType to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this NameIDType.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $element = $this->instantiateParentElement($parent);

        if ($this->NameQualifier !== null) {
            $element->setAttribute('NameQualifier', $this->NameQualifier);
        }

        if ($this->SPNameQualifier !== null) {
            $element->setAttribute('SPNameQualifier', $this->SPNameQualifier);
        }

        if ($this->value !== null) {
            $element->textContent = $this->value;
        }

        return $element;
    }
}
