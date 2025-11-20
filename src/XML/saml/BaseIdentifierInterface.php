<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\Type\SAMLStringValue;

/**
 * Interface for BaseID objects.
 *
 * @package simplesamlphp/saml2
 */
interface BaseIdentifierInterface extends IdentifierInterface
{
    /**
     * Get the value of the NameQualifier attribute of an identifier.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getNameQualifier(): ?SAMLStringValue;


    /**
     * Get the value of the SPNameQualifier attribute of an identifier.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getSPNameQualifier(): ?SAMLStringValue;
}
