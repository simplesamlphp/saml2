<?php

declare(strict_types=1);

namespace SAML2\Exception\Protocol;

use SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error indicating that the SAML responder or SAML authority is able to process the request
 *   but has chosen not to respond.
 *
 * @package simplesamlphp/saml2
 */
class RequestDeniedException extends ProtocolViolationException
{
}
