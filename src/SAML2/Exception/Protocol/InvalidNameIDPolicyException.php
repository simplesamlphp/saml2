<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that the provider cannot or will not support the requested NameIDPolicy.
 *
 * @package simplesamlphp/saml2
 */
class InvalidNameIDPolicyException extends ProtocolViolationException
{
    public const DEFAULT_MESSAGE = 'Invalid NameID policy.';
}
