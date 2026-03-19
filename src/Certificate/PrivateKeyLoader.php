<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate;

use SimpleSAML\SAML2\Configuration\DecryptionProvider;
use SimpleSAML\SAML2\Configuration\PrivateKey as PrivateKeyConfiguration;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;

class PrivateKeyLoader
{
    /**
     * Loads a private key based on the configuration given.
     *
     * @param \SimpleSAML\SAML2\Configuration\PrivateKey $key
     * @return \SimpleSAML\XMLSecurity\Key\PrivateKey
     */
    public function loadPrivateKey(PrivateKeyConfiguration $key): PrivateKey
    {
        return PrivateKey::fromFile(
            $key->isFile() ? $key->getFilePath() : $key->getContents(),
            $key->getPassPhrase(),
        );
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\DecryptionProvider $identityProvider
     * @param \SimpleSAML\SAML2\Configuration\DecryptionProvider $serviceProvider
     * @return \SimpleSAML\SAML2\Utilities\ArrayCollection
     *
     * @throws \Exception
     */
    public function loadDecryptionKeys(
        DecryptionProvider $identityProvider,
        DecryptionProvider $serviceProvider,
    ): ArrayCollection {
        $decryptionKeys = new ArrayCollection();

        $senderSharedKey = $identityProvider->getSharedKey();
        if ($senderSharedKey !== null) {
            $key = new SymmetricKey($senderSharedKey);
            $decryptionKeys->add($key);

            return $decryptionKeys;
        }

        $newPrivateKey = $serviceProvider->getPrivateKey(PrivateKeyConfiguration::NAME_NEW);
        if ($newPrivateKey instanceof PrivateKeyConfiguration) {
            $loadedKey = $this->loadPrivateKey($newPrivateKey);
            $decryptionKeys->add($loadedKey);
        }

        $privateKey = $serviceProvider->getPrivateKey(PrivateKeyConfiguration::NAME_DEFAULT, true);
        $loadedKey  = $this->loadPrivateKey($privateKey);
        $decryptionKeys->add($loadedKey);

        return $decryptionKeys;
    }
}
