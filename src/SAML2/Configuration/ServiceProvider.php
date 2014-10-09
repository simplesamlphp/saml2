<?php

/**
 * Basic Configuration Wrapper
 */
class SAML2_Configuration_ServiceProvider extends SAML2_Configuration_ArrayAdapter implements
    SAML2_Configuration_CertificateProvider,
    SAML2_Configuration_DecryptionProvider,
    SAML2_Configuration_EntityIdProvider
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

    public function getEntityId()
    {
        return $this->get('entityId');
    }

    public function isAssertionEncrypted()
    {
        return $this->get('assertionEncryptionEnabled');
    }
}
