<?php

interface SAML2_Configuration_DecryptionProvider
{
    /**
     * @return null|bool
     */
    public function isAssertionEncrypted();
}
