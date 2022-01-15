<?php

declare(strict_types=1);

namespace SAML2\Exception\Protocol;

use SAML2\Exception\ProtocolViolationException;

/**
 * A SAML error used by a session authority to indicate to a session participant
 *   that it was not able to propagate logout to all other session participants.
 *
 * @package simplesamlphp/saml2
 */
class PartialLogoutException extends ProtocolViolationException
{
}
