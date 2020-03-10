<?php

declare(strict_types=1);

namespace SAML2\XML;

/**
 * Trait grouping common functionality for elements implementing BaseIDAbstractType and NameIDType.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
trait IDNameQualifiersTrait
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
    private function setNameQualifier(?string $nameQualifier): void
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
    private function setSPNameQualifier(?string $spNameQualifier): void
    {
        $this->SPNameQualifier = $spNameQualifier;
    }
}
