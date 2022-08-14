<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\Utils\Security;
use SimpleSAML\XMLSecurity\XML\EncryptedElementInterface;
use SimpleSAML\XMLSecurity\XML\EncryptedElementTrait;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;

/**
 * Class handling encrypted attributes.
 *
 * @package simplesamlphp/saml2
 */
class EncryptedAttribute extends AbstractSamlElement implements EncryptedElementInterface
{
    use EncryptedElementTrait;


    public function getBlacklistedAlgorithms(): ?array
    {
        $container = ContainerSingleton::getInstance();
        return $container->getBlacklistedEncryptionAlgorithms();
    }


    public function getEncryptionBackend(): ?EncryptionBackend
    {
        // return the encryption backend you want to use,
        // or null if you are fine with the default
    }


    /**
     * @inheritDoc
     *
     * @return \SimpleSAML\SAML2\XML\saml\Attribute
     * @throws \Exception
     */
    public function decrypt(EncryptionAlgorithmInterface $decryptor): Attribute
    {
//        $attrXML = Security::decryptElement($this->encryptedData->toXML(), $key, $blacklist);

//        return Attribute::fromXML($attrXML);
    }
}
