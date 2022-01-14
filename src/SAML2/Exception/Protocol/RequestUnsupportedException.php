<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that the SAML responder or SAML authority does not support the request.
 *
 * @package simplesamlphp/saml2
 */
class RequestUnsupportedException extends ProtocolViolationException
{
}
