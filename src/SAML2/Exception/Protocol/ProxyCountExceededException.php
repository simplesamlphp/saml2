<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that the responding provider cannot authenticate the principal
 *   and is not permitted to proxy the request further.
 *
 * @package simplesamlphp/saml2
 */
class ProxyCountExceededException extends ProtocolViolationException
{
    public const DEFAULT_MESSAGE = 'Proxy count exceeded.';
}
