<?php

interface SAML2_Configuration_DecryptionProvider
{
    /**
     * @return null|bool
     */
    public function isAssertionEncrypted();

    /**
     * @return null|string
     */
    public function getSharedKey();

    /**
     * @param null|string $name
     *
     * @return mixed
     */
    public function getPrivateKey($name = null);
}
