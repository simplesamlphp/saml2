<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that the SAML provider cannot properly fullfil the request using
 *   the protocol binding specified in the request.
 *
 * @package simplesamlphp/saml2
 */
class UnsupportedBindingException extends ProtocolViolationException
{
    public const DEFAULT_MESSAGE = 'Unsupported binding.';
}
