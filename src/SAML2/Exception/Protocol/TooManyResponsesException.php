<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that the response message would contain more elements than
 *   the SAML responder is able to return.
 *
 * @package simplesamlphp/saml2
 */
class TooManyResponsesException extends ProtocolViolationException
{
}
