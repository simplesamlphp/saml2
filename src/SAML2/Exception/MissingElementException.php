<?php

declare(strict_types=1);

namespace SAML2\Exception;

/**
 * This exception may be raised when the passed DOMElement is missing mandatory child-elements
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class MissingElementException extends ProtocolViolationException
{
}
