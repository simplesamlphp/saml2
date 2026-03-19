<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that the resource value provided in the request
 *   message is invalid or unrecognized.
 *
 * @package simplesamlphp/saml2
 */
class ResourceNotRecognizedException extends ProtocolViolationException
{
    public const string DEFAULT_MESSAGE = 'Resource not recognized.';
}
