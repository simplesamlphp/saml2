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
    private $filePathOrContents;

    /**
     * @var string
     */
    private $passphrase;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isFile;

    public function __construct($filePathOrContents, $name, $passphrase = null, $isFile = true)
    {
        if (!is_string($filePathOrContents)) {
            throw InvalidArgumentException::invalidType('string', $filePathOrContents);
        }

        if (!is_string($name)) {
            throw InvalidArgumentException::invalidType('string', $name);
        }

        if ($passphrase && !is_string($passphrase)) {
            throw InvalidArgumentException::invalidType('string', $passphrase);
        }

        $this->filePathOrContents = $filePathOrContents;
        $this->passphrase = $passphrase;
        $this->name = $name;
        $this->isFile = (bool)$isFile;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        if (!$this->isFile()) {
            return null;
        }

        return $this->filePathOrContents;
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

    /**
     * @return string
     */
    public function getContents()
    {
        if ($this->isFile()) {
            return null;
        }

        return $this->filePathOrContents;
    }

    /**
     * @return bool
     */
    public function isFile()
    {
        return $this->isFile;
    }
}
