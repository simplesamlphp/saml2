<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception;

/**
 * Exception to be raised when no metadata was found for a specific entityID
 */
class MetadataNotFoundException extends RuntimeException
{
}
