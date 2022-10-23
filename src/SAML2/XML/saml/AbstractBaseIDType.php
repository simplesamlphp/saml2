<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;

/**
 * SAML BaseID data type.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractBaseIDType extends AbstractSamlElement implements BaseIdentifierInterface
{
    /**
     * The security or administrative domain that qualifies the identifier.
     * This attribute provides a means to federate identifiers from disparate user stores without collision.
     *
     * @see saml-core-2.0-os
     *
     * @var string|null
     */
    protected ?string $NameQualifier = null;

    /**
     * Further qualifies an identifier with the name of a service provider or affiliation of providers.
     * This attribute provides an additional means to federate identifiers on the basis of the relying party or parties.
     *
     * @see saml-core-2.0-os
     *
     * @var string|null
     */
    protected ?string $SPNameQualifier = null;


    /**
     * Initialize a saml:BaseIDAbstractType from scratch
     *
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    protected function __construct(
        ?string $NameQualifier = null,
        ?string $SPNameQualifier = null
    ) {
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
     */
    private function setNameQualifier(?string $nameQualifier): void
    {
        Assert::nullOrNotWhitespaceOnly($nameQualifier);
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
     */
    private function setSPNameQualifier(?string $spNameQualifier): void
    {
        Assert::nullOrNotWhitespaceOnly($spNameQualifier);
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
        $e = $this->instantiateParentElement($parent);

        if ($this->NameQualifier !== null) {
            $e->setAttribute('NameQualifier', $this->NameQualifier);
        }

        if ($this->SPNameQualifier !== null) {
            $e->setAttribute('SPNameQualifier', $this->SPNameQualifier);
        }

        return $e;
    }
}
