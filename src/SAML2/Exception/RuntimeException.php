<?php

declare(strict_types=1);

namespace SAML2\Exception;

use RuntimeException as BuiltinRuntimeException;
use Throwable;

/**
 * Named exception
 */
class RuntimeException extends BuiltinRuntimeException implements Throwable
{
}
