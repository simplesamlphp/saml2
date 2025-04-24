<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response\Exception;

use RuntimeException;
use Throwable;

class UnsignedResponseException extends RuntimeException implements Throwable
{
}
