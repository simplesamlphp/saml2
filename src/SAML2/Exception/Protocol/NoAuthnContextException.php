<?php

declare(strict_types=1);

namespace SAML2\Exception\Protocol;

use SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that none of the requested AuthnContexts can be used.
 *
 * @package simplesamlphp/saml2
 */
class NoAuthnContextException extends ProtocolViolationException
{
}
