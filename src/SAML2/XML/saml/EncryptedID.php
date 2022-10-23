<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use InvalidArgumentException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\ElementInterface;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\XML\EncryptedElementInterface;
use SimpleSAML\XMLSecurity\XML\EncryptedElementTrait;

use function implode;

/**
 * Class representing an encrypted identifier.
 *
 * @package simplesamlphp/saml2
 */
class EncryptedID extends AbstractSamlElement implements EncryptedElementInterface, IdentifierInterface
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
        return null;
    }


    /**
     * @inheritDoc
     *
     * @return \SimpleSAML\XML\ElementInterface
     * @throws \InvalidArgumentException
     */
    public function decrypt(EncryptionAlgorithmInterface $decryptor): ElementInterface
    {
        $xml = DOMDocumentFactory::fromString($this->decryptData($decryptor))->documentElement;

        $id = implode(':', [$xml->namespaceURI, $xml->localName]);
        switch ($id) {
            case NameID::NS . ':NameID':
                return NameID::fromXML($xml);
            case Issuer::NS . ':Issuer':
                return Issuer::fromXML($xml);
            case AbstractBaseID::NS . ':BaseID':
                return AbstractBaseID::fromXML($xml);
            default:
                // Fall thru
        }
        throw new InvalidArgumentException('Unknown or unsupported encrypted identifier.');
    }
}
