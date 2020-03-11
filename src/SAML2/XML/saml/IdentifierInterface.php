<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;

/**
 * Interface for several types of identifiers.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
interface IdentifierInterface
{
    public function getValue(): string;
}
