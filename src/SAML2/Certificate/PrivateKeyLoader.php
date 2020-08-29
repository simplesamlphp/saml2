<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate;

use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\Certificate\PrivateKey;
use SimpleSAML\SAML2\Configuration\DecryptionProvider;
use SimpleSAML\SAML2\Configuration\PrivateKey as PrivateKeyConfiguration;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\SAML2\Utilities\File;

class PrivateKeyLoader
{
    /**
     * Loads a private key based on the configuration given.
     *
     * @param \SimpleSAML\SAML2\Configuration\PrivateKey $key
     * @return \SimpleSAML\SAML2\Certificate\PrivateKey
     */
    public function loadPrivateKey(PrivateKeyConfiguration $key): PrivateKey
    {
        if ($key->isFile()) {
            $privateKey = File::getFileContents($key->getFilePath());
        } else {
            $privateKey = $key->getContents();
        }

        return PrivateKey::create($privateKey, $key->getPassPhrase());
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\DecryptionProvider $identityProvider
     * @param \SimpleSAML\SAML2\Configuration\DecryptionProvider $serviceProvider
     * @throws \Exception
     * @return \SimpleSAML\SAML2\Utilities\ArrayCollection
     */
    public function loadDecryptionKeys(
        DecryptionProvider $identityProvider,
        DecryptionProvider $serviceProvider
    ): ArrayCollection {
        $decryptionKeys = new ArrayCollection();

        $senderSharedKey = $identityProvider->getSharedKey();
        if ($senderSharedKey !== null) {
            $key = new XMLSecurityKey(XMLSecurityKey::AES128_CBC);
            $key->loadKey($senderSharedKey);
            $decryptionKeys->add($key);

            return $decryptionKeys;
        }

        $newPrivateKey = $serviceProvider->getPrivateKey(PrivateKeyConfiguration::NAME_NEW);
        if ($newPrivateKey instanceof PrivateKeyConfiguration) {
            $loadedKey = $this->loadPrivateKey($newPrivateKey);
            $decryptionKeys->add($this->convertPrivateKeyToRsaKey($loadedKey));
        }

        $privateKey = $serviceProvider->getPrivateKey(PrivateKeyConfiguration::NAME_DEFAULT, true);
        $loadedKey  = $this->loadPrivateKey($privateKey);
        $decryptionKeys->add($this->convertPrivateKeyToRsaKey($loadedKey));

        return $decryptionKeys;
    }


    /**
     * @param \SimpleSAML\SAML2\Certificate\PrivateKey $privateKey
     * @throws \Exception
     * @return \RobRichards\XMLSecLibs\XMLSecurityKey
     */
    private function convertPrivateKeyToRsaKey(PrivateKey $privateKey): XMLSecurityKey
    {
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_1_5, ['type' => 'private']);
        $passphrase = $privateKey->getPassphrase();
        if ($passphrase) {
            $key->passphrase = $passphrase;
        }

        $key->loadKey($privateKey->getKeyAsString());

        return $key;
    }
}
