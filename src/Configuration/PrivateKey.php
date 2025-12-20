<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Configuration;

use SimpleSAML\SAML2\Assert\Assert;

/**
 * Configuration of a private key.
 */
class PrivateKey extends ArrayAdapter
{
    public const string NAME_NEW = 'new';

    public const string NAME_DEFAULT = 'default';


    /**
     * Constructor for PrivateKey.
     */
    public function __construct(
        private string $filePathOrContents,
        private string $name,
        private string $passphrase = '',
        private bool $isFile = true,
    ) {
    }


    /**
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function getFilePath(): string
    {
        Assert::true($this->isFile(), 'No path provided.');

        return $this->filePathOrContents;
    }


    /**
     */
    public function hasPassPhrase(): bool
    {
        return !empty($this->passphrase);
    }


    /**
     */
    public function getPassPhrase(): string
    {
        return $this->passphrase;
    }


    /**
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    public function getContents(): string
    {
        Assert::false($this->isFile(), 'No contents provided.');

        return $this->filePathOrContents;
    }


    /**
     */
    public function isFile(): bool
    {
        return $this->isFile;
    }
}
