<?php

declare(strict_types=1);

namespace SAML2\Configuration;

/**
 * Basic configuration wrapper
 */
final class IdentityProvider extends ArrayAdapter implements
    CertificateProvider,
    DecryptionProvider,
    EntityIdProvider
{
    /**
     * @return array|\Traversable|null
     */
    public function getKeys()
    {
        return $this->get('keys');
    }


    /**
     * @return string|null
     */
    public function getCertificateData()
    {
        return $this->get('certificateData');
    }


    /**
     * @return string|null
     */
    public function getCertificateFile()
    {
        return $this->get('certificateFile');
    }


    /**
     * @return array|mixed|\Traversable|null
     */
    public function getCertificateFingerprints()
    {
        return $this->get('certificateFingerprints');
    }


    /**
     * @return bool|null
     */
    public function isAssertionEncryptionRequired()
    {
        return $this->get('assertionEncryptionEnabled');
    }


    /**
     * @return string|null
     */
    public function getSharedKey()
    {
        return $this->get('sharedKey');
    }


    /**
     * @return mixed|null
     */
    public function hasBase64EncodedAttributes()
    {
        return $this->get('base64EncodedAttributes');
    }


    /**
     * @param string $name
     * @param bool $required
     * @return mixed|null
     */
    public function getPrivateKey(string $name, bool $required = null)
    {
        if ($required === null) {
            $required = false;
        }
        $privateKeys = $this->get('privateKeys');
        $key = array_filter($privateKeys, function (PrivateKey $key) use ($name) {
            return $key->getName() === $name;
        });

        $keyCount = count($key);
        if ($keyCount !== 1 && $required) {
            throw new \RuntimeException(sprintf(
                'Attempted to get privateKey by name "%s", found "%d" keys, where only one was expected. Please '
                . 'verify that your configuration is correct',
                $name,
                $keyCount
            ));
        }

        if (!$keyCount) {
            return null;
        }

        return array_pop($key);
    }


    /**
     * @return array|null
     */
    public function getBlacklistedAlgorithms()
    {
        return $this->get('blacklistedEncryptionAlgorithms');
    }


    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->get('entityId');
    }
}
