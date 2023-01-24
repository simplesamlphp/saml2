<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Configuration;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\InvalidArgumentException;
use SimpleSAML\SAML2\Exception\RuntimeException;

/**
 * Configuration of a private key.
 */
class PrivateKey extends ArrayAdapter
{
    /** @var string */
    public const NAME_NEW = 'new';

    /** @var string */
    public const NAME_DEFAULT = 'default';


    /**
     * Constructor for PrivateKey.
     *
     * @param string $filePathOrContents
     * @param string $name
     * @param string $passphrase
     * @param bool $isFile
     */
    public function __construct(
        private string $filePathOrContents,
        private string $name,
        private string $passphrase = '',
        private bool $isFile = true
    ) {
    }


    /**
     * @return string
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function getFilePath(): string
    {
        Assert::true($this->isFile(), 'No path provided.');

        return $this->filePathOrContents;
    }


    /**
     * @return bool
     */
    public function hasPassPhrase(): bool
    {
        return !empty($this->passphrase);
    }


    /**
     * @return string
     */
    public function getPassPhrase(): string
    {
        return $this->passphrase;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function getContents(): string
    {
        Assert::false($this->isFile(), 'No contents provided.');

        return $this->filePathOrContents;
    }

    /**
     * @return bool
     */
    public function isFile(): bool
    {
        return $this->isFile;
    }
}
