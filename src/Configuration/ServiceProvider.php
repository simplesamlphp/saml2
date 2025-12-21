<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Configuration;

use RuntimeException;
use SimpleSAML\XMLSecurity\Constants as C;

use function array_filter;
use function array_pop;
use function count;
use function sprintf;

/**
 * Basic Configuration Wrapper
 */
class ServiceProvider extends ArrayAdapter implements CertificateProvider, DecryptionProvider, EntityIdProvider
{
    /**
     * @return null|array|\Traversable
     */
    public function getKeys()
    {
        return $this->get('keys');
    }


    /**
     */
    public function getCertificateData(): ?string
    {
        return $this->get('certificateData');
    }


    /**
     */
    public function getCertificateFile(): ?string
    {
        return $this->get('certificateFile');
    }


    /**
     * @return array|\Traversable|null
     */
    public function getCertificateFingerprints()
    {
        return $this->get('certificateFingerprints');
    }


    /**
     */
    public function getEntityId(): ?string
    {
        return $this->get('entityId');
    }


    /**
     */
    public function isAssertionEncryptionRequired(): ?bool
    {
        return $this->get('assertionEncryptionEnabled');
    }


    /**
     */
    public function getSharedKey(): ?string
    {
        return $this->get('sharedKey');
    }


    /**
     * @return mixed|null
     */
    public function getPrivateKey(string $name, ?bool $required = null)
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
            throw new RuntimeException(sprintf(
                'Attempted to get privateKey by name "%s", found "%d" keys, where only one was expected. Please '
                . 'verify that your configuration is correct',
                $name,
                $keyCount,
            ));
        }

        if (!$keyCount) {
            return null;
        }

        return array_pop($key);
    }


    /**
     * @return array
     */
    public function getBlacklistedAlgorithms(): array
    {
        return $this->get('blacklistedEncryptionAlgorithms', [C::KEY_TRANSPORT_RSA_1_5]);
    }
}
