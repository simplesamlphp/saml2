<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception;

use RuntimeException as BUILTIN_RuntimeException;
use Throwable;

/**
 * Named exception
 */
class RuntimeException extends BUILTIN_RuntimeException implements Throwable
{
}
