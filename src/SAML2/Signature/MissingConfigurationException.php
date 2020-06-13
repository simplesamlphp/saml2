<?php

declare(strict_types=1);

namespace SAML2\Signature;

use RuntimeException;
use SAML2\Exception\Throwable;

class MissingConfigurationException extends \RuntimeException implements Throwable
{
}
