<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that the SAML responder cannot process the request because the protocol
 *   version specified in the request message is too low.
 *
 * @package simplesamlphp/saml2
 */
class RequestVersionTooLowException extends ProtocolViolationException
{
    public const string DEFAULT_MESSAGE = 'Protocol version too low.';
}
