<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception;

use InvalidArgumentException;

/**
 * This exception may be raised when the passed DOMElement is of the wrong type
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class InvalidDOMElementException extends InvalidArgumentException
{
}
