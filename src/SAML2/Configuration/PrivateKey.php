<?php

namespace SAML2\Configuration;

use SAML2\Exception\InvalidArgumentException;

/**
 * Configuration of a private key.
 */
class PrivateKey extends ArrayAdapter
{
    const NAME_NEW     = 'new';
    const NAME_DEFAULT = 'default';

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var string
     */
    private $passphrase;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $filePath, string $name, string $passphrase = null)
    {
        $this->filePath = $filePath;
        $this->passphrase = $passphrase;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return bool
     */
    public function hasPassPhrase()
    {
        return (bool) $this->passphrase;
    }

    /**
     * @return string
     */
    public function getPassPhrase()
    {
        return $this->passphrase;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
