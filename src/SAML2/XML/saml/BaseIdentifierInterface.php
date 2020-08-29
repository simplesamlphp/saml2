<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

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
     * @return string|null
     */
    public function getNameQualifier(): ?string;


    /**
     * Get the value of the SPNameQualifier attribute of an identifier.
     *
     * @return string|null
     */
    public function getSPNameQualifier(): ?string;
}
