<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that none of the requested AuthnContexts can be used.
 *
 * @package simplesamlphp/saml2
 */
class NoAuthnContextException extends ProtocolViolationException
{
    public const string DEFAULT_MESSAGE = 'None of the requested AuthnContexts can be used.';
}
