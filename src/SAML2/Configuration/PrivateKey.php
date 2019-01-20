<?php

declare(strict_types=1);

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


    /**
     * Constructor for PrivateKey.
     *
     * @param string $filePath
     * @param string $name
     * @param string $passphrase
     * @throws \Exception
     */
    public function __construct(string $filePath, string $name, string $passphrase = '')
    {
        $this->filePath = $filePath;
        $this->passphrase = $passphrase;
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getFilePath() : string
    {
        return $this->filePath;
    }


    /**
     * @return bool
     */
    public function hasPassPhrase() : bool
    {
        return (bool) $this->passphrase;
    }


    /**
     * @return string
     */
    public function getPassPhrase() : string
    {
        return $this->passphrase;
    }


    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
}
