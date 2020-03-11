<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;

/**
 * Interface for BaseID objects.
 *
 * @package simplesamlphp/saml2
 */
interface BaseIdentifierInterface extends IdentifierInterface
{
    /**
     * @return string|null
     */
    public function getNameQualifier(): ?string;


    /**
     * @return string|null
     */
    public function getSPNameQualifier(): ?string;
}
