<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that the provider was unable to succesfully authenticate the principal.
 *
 * @package simplesamlphp/saml2
 */
class AuthnFailedException extends ProtocolViolationException
{
    public const string DEFAULT_MESSAGE = 'Authentication failed.';
}
