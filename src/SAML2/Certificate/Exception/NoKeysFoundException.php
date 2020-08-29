<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate\Exception;

use DomainException;
use Throwable;

/**
 * Named exception. Indicates that although required, no keys could be loaded from the configuration
 */
class NoKeysFoundException extends DomainException implements Throwable
{
}
