<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use InvalidArgumentException;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\XMLElementInterface;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\Utils\Security;
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
     * @return \SimpleSAML\XML\XMLElementInterface
     * @throws \InvalidArgumentException
     */
    public function decrypt(EncryptionAlgorithmInterface $decryptor): XMLElementInterface
    {
        $xml = DOMDocumentFactory::fromString($this->decryptData($decryptor))->documentElement;

        $id = implode(':', [$xml->namespaceURI, $xml->localName]);
        switch ($id) {
            case NameID::NS . ':NameID':
                return NameID::fromXML($xml);
            case Issuer::NS . ':Issuer':
                return Issuer::fromXML($xml);
            case BaseID::NS . ':BaseID':
                $xsiType = $xml->getAttributeNS(C::NS_XSI, 'type');
                $container = ContainerSingleton::getInstance();
                $handler = $container->getIdentifierHandler($xsiType);
                if ($handler !== null) {
                    return $handler::fromXML($xml);
                }
                return BaseID::fromXML($xml);
            default:
              // Fall thru
        }
        throw new InvalidArgumentException('Unknown or unsupported encrypted identifier.');
    }
}
