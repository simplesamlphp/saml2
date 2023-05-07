<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception;

use RuntimeException as BuiltinRuntimeException;
use Throwable;

/**
 * Named exception
 */
class RuntimeException extends BuiltinRuntimeException implements Throwable
{
}
