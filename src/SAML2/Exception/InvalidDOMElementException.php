<?php

declare(strict_types=1);

namespace SAML2\Exception;

use SimpleSAML\Assert\AssertionFailedException;

/**
 * This exception may be raised when the passed DOMElement is of the wrong type
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class InvalidDOMElementException extends AssertionFailedException
{
}
