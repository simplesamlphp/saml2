<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Metadata;

use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\{PrivateKey, PublicKey, SymmetricKey};

/**
 * Class holding configuration for a SAML 2 Identity Provider.
 *
 * @package simplesamlphp/saml2
 */
class IdentityProvider extends AbstractProvider
{
    /**
     */
    public function __construct(
        string $entityId,
        EncryptionAlgorithmFactory|KeyTransportAlgorithmFactory|null $encryptionAlgorithmFactory = null,
        SignatureAlgorithmFactory|null $signatureAlgorithmFactory = null,
        string $signatureAlgorithm = C::SIG_RSA_SHA256,
        array $validatingKeys = [],
        PrivateKey|null $signingKey = null,
        PublicKey|SymmetricKey|null $encryptionKey = null,
        array $decryptionKeys = [],
        array $IDPList = [],
    ) {
        parent::__construct(
            $entityId,
            $encryptionAlgorithmFactory,
            $signatureAlgorithmFactory,
            $signatureAlgorithm,
            $validatingKeys,
            $signingKey,
            $encryptionKey,
            $decryptionKeys,
            $IDPList,
        );
    }
}
