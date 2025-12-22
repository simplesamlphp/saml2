<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\XMLSchema\Type\AnyURIValue;
use SimpleSAML\XMLSchema\Type\Base64BinaryValue;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\SymmetricKey;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\EncryptableElementTrait as ParentEncryptableElementTrait;
use SimpleSAML\XMLSecurity\XML\xenc\CipherData;
use SimpleSAML\XMLSecurity\XML\xenc\CipherValue;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedData;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptionMethod;

use function in_array;

/**
 * Trait aggregating functionality for elements that are encrypted.
 *
 * @package simplesamlphp/saml2
 */
trait EncryptableElementTrait
{
    use ParentEncryptableElementTrait;


    /**
     * Encryt this object.
     *
     * @param \SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface $encryptor The encryptor to use,
     * either to encrypt the object itself, or to encrypt a session key (if the encryptor implements a key transport
     * algorithm).
     *
     * @return \SimpleSAML\XMLSecurity\XML\xenc\EncryptedData
     */
    public function encrypt(EncryptionAlgorithmInterface $encryptor): EncryptedData
    {
        $keyInfo = null;
        if (in_array($encryptor->getAlgorithmId(), C::$KEY_TRANSPORT_ALGORITHMS)) {
            // the encryptor uses a key transport algorithm, use that to generate a session key
            $sessionKey = SymmetricKey::generate($this->sessionKeyLen);

            $encryptedKey = EncryptedKey::fromKey(
                $sessionKey,
                $encryptor,
                new EncryptionMethod(
                    AnyURIValue::fromString($encryptor->getAlgorithmId()),
                ),
            );

            $keyInfo = new KeyInfo([$encryptedKey]);

            $factory = new EncryptionAlgorithmFactory(
                $this->getBlacklistedAlgorithms() ?? EncryptionAlgorithmFactory::DEFAULT_BLACKLIST,
            );
            $encryptor = $factory->getAlgorithm($this->blockCipherAlgId, $sessionKey);
            $encryptor->setBackend($this->getEncryptionBackend());
        }

        $xmlRepresentation = $this->toXML();

        return new EncryptedData(
            new CipherData(
                new CipherValue(
                    Base64BinaryValue::fromString(
                        base64_encode($encryptor->encrypt(
                            $xmlRepresentation->ownerDocument->saveXML($xmlRepresentation),
                        )),
                    ),
                ),
            ),
            null,
            AnyURIValue::fromString(C::XMLENC_ELEMENT),
            null,
            null,
            new EncryptionMethod(
                AnyURIValue::fromString($encryptor->getAlgorithmId()),
            ),
            $keyInfo,
        );
    }


    /**
     * @return array|null
     */
    public function getBlacklistedAlgorithms(): ?array
    {
        $container = ContainerSingleton::getInstance();
        return $container->getBlacklistedEncryptionAlgorithms();
    }
}
