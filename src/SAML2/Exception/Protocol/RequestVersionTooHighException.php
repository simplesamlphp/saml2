<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that the SAML responder cannot process the request because the protocol version
 *   specified in the request message is a major upgrade from the highest protocol version supported.
 *
 * @package simplesamlphp/saml2
 */
class RequestVersionTooHighException extends ProtocolViolationException
{
    public const DEFAULT_MESSAGE = 'Protocol version too high.';
}
