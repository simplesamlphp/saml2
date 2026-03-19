<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that the SAML responder cannot any requests with the protocol
 *   version specified in the request.
 *
 * @package simplesamlphp/saml2
 */
class RequestVersionDeprecatedException extends ProtocolViolationException
{
    public const string DEFAULT_MESSAGE = 'Deprecated version used.';
}
