<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

/**
 * SAML IDNameQualifier attribute group.
 *
 * @package simplesamlphp/saml2
 */
trait IDNameQualifiersTrait
{
    /**
     * Collect the value of the NameQualifier-property
     *
     * @return string|null
     */
    public function getNameQualifier(): ?string
    {
        return $this->nameQualifier;
    }


    /**
     * Collect the value of the SPNameQualifier-property
     *
     * @return string|null
     */
    public function getSPNameQualifier(): ?string
    {
        return $this->spNameQualifier;
    }
}
