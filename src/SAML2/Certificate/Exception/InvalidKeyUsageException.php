<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate\Exception;

use InvalidArgumentException;
use SimpleSAML\SAML2\Certificate\Key;
use Throwable;

use function implode;
use function sprintf;

/**
 * Named exception for when a non-existent key-usage is given
 */
class InvalidKeyUsageException extends InvalidArgumentException implements Throwable
{
    /**
     * @param string $usage
     */
    public function __construct(string $usage)
    {
        $message = sprintf(
            'Invalid key usage given: "%s", usages "%s" allowed',
            $usage,
            implode('", "', Key::getValidKeyUsages())
        );

        parent::__construct($message);
    }
}
