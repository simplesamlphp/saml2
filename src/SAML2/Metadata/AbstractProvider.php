<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Metadata;

use SimpleSAML\Assert\Assert;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\{PrivateKey, PublicKey, SymmetricKey};

use function array_keys;

/**
 * Class holding common configuration for SAML2 entities.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractProvider
{
    /**
     */
    protected function __construct(
        protected string $entityId,
        protected string $signatureAlgorithm,
        protected array $validatingKeys,
        protected ?PrivateKey $signingKey,
        protected ?PublicKey $encryptionKey,
        protected array $decryptionKeys,
        protected ?SymmetricKey $preSharedKey,
        protected string $preSharedKeyAlgorithm,
        protected array $IDPList,
    ) {
        Assert::validURI($entityId);
        Assert::validURI($signatureAlgorithm);
        Assert::oneOf($signatureAlgorithm, array_keys(C::$RSA_DIGESTS));
        Assert::allIsInstanceOf($decryptionKeys, PrivateKey::class);
        Assert::allIsInstanceOf($validatingKeys, PublicKey::class);
        Assert::allValidURI($IDPList);
        Assert::nullOrValidURI($preSharedKeyAlgorithm);
        Assert::oneOf($preSharedKeyAlgorithm, array_keys(C::$BLOCK_CIPHER_ALGORITHMS));
    }


    /**
     * Retrieve the signature slgorithm to be used for signing messages.
     */
    public function getSignatureAlgorithm(): string
    {
        return $this->signatureAlgorithm;
    }


    /**
     * Get the private key to use for signing messages.
     *
     * @return \SimpleSAML\XMLSecurity\Key\PrivateKey|null
     */
    public function getSigningKey(): ?PrivateKey
    {
        return $this->signingKey;
    }


    /**
     * Get the validating keys to verify a message signature with.
     *
     * @return array<\SimpleSAML\XMLSecurity\Key\PublicKey>
     */
    public function getValidatingKeys(): array
    {
        return $this->validatingKeys;
    }


    /**
     * Get the public key to use for encrypting messages.
     *
     * @return \SimpleSAML\XMLSecurity\Key\PublicKey|null
     */
    public function getEncryptionKey(): ?PublicKey
    {
        return $this->encryptionKey;
    }


    /**
     * Get the symmetric key to use for encrypting/decrypting messages.
     *
     * @return \SimpleSAML\XMLSecurity\Key\SymmetricKey|null
     */
    public function getPreSharedKey(): ?SymmetricKey
    {
        return $this->preSharedKey;
    }


    /**
     * Get the symmetric encrypting/decrypting algorithm to use.
     *
     * @return string|null
     */
    public function getPreSharedKeyAlgorithm(): ?string
    {
        return $this->preSharedKeyAlgorithm;
    }


    /**
     * Get the decryption keys to decrypt the assertion with.
     *
     * @return array<\SimpleSAML\XMLSecurity\Key\PrivateKey>
     */
    public function getDecryptionKeys(): array
    {
        return $this->decryptionKeys;
    }


    /**
     * Retrieve the configured entity ID for this entity
     */
    public function getEntityId(): string
    {
        return $this->entityId;
    }


    /**
     * Retrieve the configured IDPList for this entity.
     *
     * @return string[]
     */
    public function getIDPList(): array
    {
        return $this->IDPList;
    }
}
