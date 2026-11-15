<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Metadata;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\md\AssertionConsumerService;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\{PrivateKey, PublicKey, SymmetricKey};

/**
 * Class holding configuration for a SAML 2 Service Provider.
 *
 * @package simplesamlphp/saml2
 */
class ServiceProvider extends AbstractProvider
{
    /**
     */
    public function __construct(
        string $entityId,
        string $signatureAlgorithm = C::SIG_RSA_SHA256,
        array $validatingKeys = [],
        ?PrivateKey $signingKey = null,
        ?PublicKey $encryptionKey = null,
        protected array $assertionConsumerService = [],
        array $decryptionKeys = [],
        ?SymmetricKey $preSharedKey = null,
        string $preSharedKeyAlgorithm = C::BLOCK_ENC_AES256_GCM,
        array $IDPList = [],
        protected bool $wantAssertionsSigned = false, // Default false by specification
    ) {
        Assert::allIsInstanceOf($assertionConsumerService, AssertionConsumerService::class);

        parent::__construct(
            $entityId,
            $signatureAlgorithm,
            $validatingKeys,
            $signingKey,
            $encryptionKey,
            $decryptionKeys,
            $preSharedKey,
            $preSharedKeyAlgorithm,
            $IDPList,
        );
    }


    /**
     * Retrieve the configured ACS-endpoints for this Service Provider.
     *
     * @return array<\SimpleSAML\SAML2\XML\md\AssertionConsumerService>
     */
    public function getAssertionConsumerService(): array
    {
        return $this->assertionConsumerService;
    }


    /**
     * Retrieve the configured value for whether assertions must be signed.
     *
     * @return bool
     */
    public function getWantAssertionsSigned(): bool
    {
        return $this->wantAssertionsSigned;
    }
}
