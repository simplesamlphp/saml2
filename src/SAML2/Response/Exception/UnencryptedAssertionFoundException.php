<?php

declare(strict_types=1);

namespace SAML2\Response\Exception;

use RuntimeException;
use Throwable;

class UnencryptedAssertionFoundException extends RuntimeException implements Throwable
{
}
