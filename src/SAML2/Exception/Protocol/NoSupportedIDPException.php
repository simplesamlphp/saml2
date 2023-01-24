<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error used by an intermediary to indicate that none of the identity providers
 *   in an IDPList are supported by the intermediary.
 *
 * @package simplesamlphp/saml2
 */
class NoSupportedIDPException extends ProtocolViolationException
{
    public const DEFAULT_MESSAGE = 'No supported IdP.';
}
