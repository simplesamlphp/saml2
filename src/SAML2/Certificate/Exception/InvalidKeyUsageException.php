<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate\Exception;

use InvalidArgumentException;
use Throwable;

/**
 * Named exception for when a non-existent key-usage is given
 */
class InvalidKeyUsageException extends InvalidArgumentException implements Throwable
{
}
