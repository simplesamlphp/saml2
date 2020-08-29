<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception;

/**
 * This exception may be raised when the passed DOMElement contains too much child-elements of a certain type
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class TooManyElementsException extends ProtocolViolationException
{
}
