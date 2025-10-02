<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate;

use ArrayAccess;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Certificate\Exception\InvalidKeyUsageException;
use SimpleSAML\SAML2\Exception\InvalidArgumentException;

use function array_key_exists;
use function is_string;

/**
 * Simple DTO wrapper for (X509) keys. Implements ArrayAccess
 * for easier backwards compatibility.
 */
class Key implements ArrayAccess
{
    // Possible key usages
    public const USAGE_SIGNING = 'signing';

    public const USAGE_ENCRYPTION = 'encryption';


    /** @var array */
    protected array $keyData = [];


    /**
     * @param array $keyData
     */
    public function __construct(array $keyData)
    {
        // forcing usage of offsetSet
        foreach ($keyData as $property => $value) {
            $this->offsetSet($property, $value);
        }
    }


    /**
     * Whether or not the key is configured to be used for usage given
     *
     * @param string $usage
     * @return bool
     * @throws \SimpleSAML\SAML2\Exception\InvalidArgumentException
     */
    public function canBeUsedFor(string $usage): bool
    {
        Assert::oneOf(
            $usage,
            [self::USAGE_ENCRYPTION, self::USAGE_SIGNING],
            'Invalid key usage given: "%s", usages "%2$s" allowed',
            InvalidKeyUsageException::class,
        );

        return isset($this->keyData[$usage]) && $this->keyData[$usage];
    }


    /**
     * @param mixed $offset
     * @throws \SimpleSAML\SAML2\Exception\InvalidArgumentException
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        if (!is_string($offset)) {
            throw InvalidArgumentException::invalidType('string', $offset);
        }
        return array_key_exists($offset, $this->keyData);
    }


    /**
     * @param mixed $offset
     * @throws \SimpleSAML\SAML2\Exception\InvalidArgumentException
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        if (!is_string($offset)) {
            throw InvalidArgumentException::invalidType('string', $offset);
        }
        return $this->keyData[$offset];
    }


    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws \SimpleSAML\SAML2\Exception\InvalidArgumentException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!is_string($offset)) {
            throw InvalidArgumentException::invalidType('string', $offset);
        }
        $this->keyData[$offset] = $value;
    }


    /**
     * @param mixed $offset
     * @throws \SimpleSAML\SAML2\Exception\InvalidArgumentException
     */
    public function offsetUnset(mixed $offset): void
    {
        if (!is_string($offset)) {
            throw InvalidArgumentException::invalidType('string', $offset);
        }
        unset($this->keyData[$offset]);
    }
}
