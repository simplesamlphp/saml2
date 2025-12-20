<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Configuration;

use function array_key_exists;

/**
 * Default implementation for configuration
 */
class ArrayAdapter implements Queryable
{
    /**
     * @param array $configuration
     */
    public function __construct(
        private array $configuration,
    ) {
    }


    /**
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function get(string $key, $defaultValue = null)
    {
        if (!$this->has($key)) {
            return $defaultValue;
        }

        return $this->configuration[$key];
    }


    /**
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->configuration);
    }
}
