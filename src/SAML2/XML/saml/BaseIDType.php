<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Base class corresponding to the BaseID element.
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */

abstract class BaseIDType extends AbstractSamlElement
{
    /**
     * The security or administrative domain that qualifies the identifier.
     * This attribute provides a means to federate identifiers from disparate user stores without collision.
     *
     * @see saml-core-2.0-os
     *
     * @var string|null
     */
    protected $NameQualifier = null;

    /**
     * Further qualifies an identifier with the name of a service provider or affiliation of providers.
     * This attribute provides an additional means to federate identifiers on the basis of the relying party or parties.
     *
     * @see saml-core-2.0-os
     *
     * @var string|null
     */
    protected $SPNameQualifier = null;


    /**
     * Initialize a saml:BaseID
     *
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    public function __construct(?string $NameQualifier = null, ?string $SPNameQualifier = null)
    {
        $this->setNameQualifier($NameQualifier);
        $this->setSPNameQualifier($SPNameQualifier);
    }


    /**
     * Collect the value of the NameQualifier-property
     *
     * @return string|null
     */
    public function getNameQualifier(): ?string
    {
        return $this->NameQualifier;
    }


    /**
     * Set the value of the NameQualifier-property
     *
     * @param string|null $nameQualifier
     * @return void
     */
    protected function setNameQualifier(?string $nameQualifier): void
    {
        $this->NameQualifier = $nameQualifier;
    }


    /**
     * Collect the value of the SPNameQualifier-property
     *
     * @return string|null
     */
    public function getSPNameQualifier(): ?string
    {
        return $this->SPNameQualifier;
    }


    /**
     * Set the value of the SPNameQualifier-property
     *
     * @param string|null $spNameQualifier
     * @return void
     */
    protected function setSPNameQualifier(?string $spNameQualifier): void
    {
        $this->SPNameQualifier = $spNameQualifier;
    }


    /**
     * Convert this BaseID to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this BaseID.
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

        return $element;
    }


    /**
     * Get a string representation of this BaseIDType object.
     *
     * @return string The resulting XML, as a string.
     */
    public function __toString(): string
    {
        $doc = DOMDocumentFactory::create();
        $root = $doc->createElementNS(Constants::NS_SAML, 'root');
        $ele = $this->toXML($root);

        return $doc->saveXML($ele);
    }
}
