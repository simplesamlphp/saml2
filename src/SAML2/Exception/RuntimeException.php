<?php

declare(strict_types=1);

namespace SAML2\Exception;

use RuntimeException as BUILTIN_RuntimeException;

/**
 * Named exception
 */
class RuntimeException extends BUILTIN_RuntimeException implements Throwable
{
}
