<?php

declare(strict_types=1);

namespace SAML2\Configuration;

interface DecryptionProvider
{
    /**
     * @return null|bool
     */
    public function isAssertionEncryptionRequired();

    /**
     * @return null|string
     */
    public function getSharedKey();

    /**
     * @param string  $name     the name of the private key
     * @param boolean $required whether or not the private key must exist
     *
     * @return mixed
     */
    public function getPrivateKey(string $name, bool $required = false);

    /**
     * @return array
     */
    public function getBlacklistedAlgorithms();
}
