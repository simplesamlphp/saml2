<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate;

use SimpleSAML\SAML2\Certificate\Exception\InvalidKeyUsageException;
use SimpleSAML\SAML2\Exception\InvalidArgumentException;

/**
 * Simple DTO wrapper for (X509) keys. Implements ArrayAccess
 * for easier backwards compatibility.
 */
class Key implements \ArrayAccess
{
    // Possible key usages
    public const USAGE_SIGNING = 'signing';

    public const USAGE_ENCRYPTION = 'encryption';

    /** @var array */
    protected $keyData = [];


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
        if (!in_array($usage, static::getValidKeyUsages(), true)) {
            throw new InvalidKeyUsageException($usage);
        }

        return isset($this->keyData[$usage]) && $this->keyData[$usage];
    }


    /**
     * Returns the list of valid key usage options
     * @return array
     */
    public static function getValidKeyUsages(): array
    {
        return [
            self::USAGE_ENCRYPTION,
            self::USAGE_SIGNING
        ];
    }


    /**
     * @param mixed $offset
     * @throws \SimpleSAML\SAML2\Exception\InvalidArgumentException
     * @return bool
     *
     * Type hint not possible due to upstream method signature
     */
    public function offsetExists($offset): bool
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
     *
     * Type hint not possible due to upstream method signature
     */
    public function offsetGet($offset)
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
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (!is_string($offset)) {
            throw InvalidArgumentException::invalidType('string', $offset);
        }
        $this->keyData[$offset] = $value;
    }


    /**
     * @param mixed $offset
     * @throws \SimpleSAML\SAML2\Exception\InvalidArgumentException
     * @return void
     *
     * Type hint not possible due to upstream method signature
     */
    public function offsetUnset($offset): void
    {
        if (!is_string($offset)) {
            throw InvalidArgumentException::invalidType('string', $offset);
        }
        unset($this->keyData[$offset]);
    }
}
