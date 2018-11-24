<?php

declare(strict_types=1);

namespace SAML2\Certificate;

final class PrivateKey extends Key
{
    public static function create(string $keyContents, string $passphrase = null)
    {
        $keyData = ['PEM' => $keyContents, self::USAGE_ENCRYPTION => true];
        if (is_string($passphrase)) {
            $keyData['passphrase'] = $passphrase;
        }

        return new self($keyData);
    }

    /**
     * @return string
     */
    public function getKeyAsString()
    {
        return $this->keyData['PEM'];
    }

    public function getPassphrase()
    {
        return isset($this->keyData['passphrase']) ? $this->keyData['passphrase'] : null;
    }
}
