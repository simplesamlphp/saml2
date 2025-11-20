<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\Type\SAMLStringValue;

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
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getNameQualifier(): ?SAMLStringValue
    {
        return $this->NameQualifier;
    }


    /**
     * Collect the value of the SPNameQualifier-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getSPNameQualifier(): ?SAMLStringValue
    {
        return $this->SPNameQualifier;
    }
}
