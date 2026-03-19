<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating the responding provider does not recognize the principal
 *   specified or implied by the request.
 *
 * @package simplesamlphp/saml2
 */
class UnknownPrincipalException extends ProtocolViolationException
{
    public const string DEFAULT_MESSAGE = 'Unknown principal.';
}
