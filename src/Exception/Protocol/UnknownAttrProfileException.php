<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that an entity that has no knowledge of a particular attribute profile
 *   has been presented with an attribute drawn from that profile.
 *
 * @package simplesamlphp/saml2
 */
class UnknownAttrProfileException extends ProtocolViolationException
{
    public const string DEFAULT_MESSAGE = 'Unknown attribute profile.';
}
