<?php

/**
 * Basic configuration wrapper
 */
class SAML2_Configuration_IdentityProvider extends SAML2_Configuration_ArrayAdapter implements
    SAML2_Configuration_CertificateProvider,
    SAML2_Configuration_DecryptionProvider
{
    public function getKeys()
    {
        return $this->get('keys');
    }

    public function getCertificateData()
    {
        return $this->get('certificateData');
    }

    public function getCertificateFile()
    {
        return $this->get('certificateFile');
    }

    public function getCertificateFingerprints()
    {
        return $this->get('certificateFingerprints');
    }

    public function isAssertionEncrypted()
    {
        return $this->get('assertionEncryptionEnabled');
    }
}
