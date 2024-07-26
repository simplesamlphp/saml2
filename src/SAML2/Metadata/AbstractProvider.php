<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Metadata;

use SimpleSAML\Assert\Assert;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\{PrivateKey, PublicKey, SymmetricKey};

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
        protected EncryptionAlgorithmFactory|KeyTransportAlgorithmFactory|null $encryptionAlgorithmFactory,
        protected SignatureAlgorithmFactory|null $signatureAlgorithmFactory,
        protected string $signatureAlgorithm,
        protected array $validatingKeys,
        protected PrivateKey|null $signingKey,
        protected PublicKey|SymmetricKey|null $encryptionKey,
        protected array $decryptionKeys,
        protected array $IDPList,
    ) {
        Assert::validURI($entityId);
        Assert::validURI($signatureAlgorithm);
        Assert::allIsInstanceOfAny($decryptionKeys, [SymmetricKey::class, PrivateKey::class]);
        Assert::allIsInstanceOf($validatingKeys, PublicKey::class);
        Assert::allValidURI($IDPList);
    }


    /**
     * Retrieve the SignatureAlgorithmFactory used for signing and verifying messages.
     */
    public function getSignatureAlgorithmFactory(): ?SignatureAlgorithmFactory
    {
        return $this->signatureAlgorithmFactory;
    }


    /**
     * Retrieve the EncryptionAlgorithmFactory used for encrypting and decrypting messages.
     */
    public function getEncryptionAlgorithmFactory(): EncryptionAlgorithmFactory|KeyTransportAlgorithmFactory|null
    {
        return $this->encryptionAlgorithmFactory;
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
     * Get the private key to use for signing messages.
     *
     * @return \SimpleSAML\XMLSecurity\Key\PublicKey|\SimpleSAML\XMLSecurity\Key\SymmetricKey|null
     */
    public function getEncryptionKey(): PublicKey|SymmetricKey|null
    {
        return $this->encryptionKey;
    }


    /**
     * Get the decryption keys to decrypt the assertion with.
     *
     * @return array<\SimpleSAML\XMLSecurity\Key\PrivateKey|\SimpleSAML\XMLSecurity\Key\SymmetricKey>
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
