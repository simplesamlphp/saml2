<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate\Exception;

use DomainException;
use Throwable;

/**
 * Named Exception for what the name describes. This should not occur, as it has to be
 * caught on the configuration side.
 */
class InvalidCertificateStructureException extends DomainException implements Throwable
{
}
