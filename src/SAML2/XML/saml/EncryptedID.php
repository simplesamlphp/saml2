<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use InvalidArgumentException;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Compat\ContainerSingleton;
use SAML2\Constants;
use SAML2\XML\AbstractXMLElement;
use SAML2\XML\EncryptedElementInterface;
use SAML2\XML\EncryptedElementTrait;
use SAML2\Utils;

/**
 * Class representing an encrypted identifier.
 *
 * @package simplesamlphp/saml2
 */
class EncryptedID extends AbstractSamlElement implements EncryptedElementInterface, IdentifierInterface
{
    use EncryptedElementTrait;


    /**
     * @inheritDoc
     *
     * @return \SAML2\XML\saml\IdentifierInterface
     * @throws \InvalidArgumentException
     *
     * @psalm-suppress MismatchingDocblockReturnType
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function decrypt(XMLSecurityKey $key, array $blacklist = []): AbstractXMLElement
    {
        $xml = Utils::decryptElement($this->encryptedData->toXML(), $key, $blacklist);
        $id = implode(':', [$xml->namespaceURI, $xml->localName]);
        switch ($id) {
            case NameID::NS . ':NameID':
                return NameID::fromXML($xml);
            case Issuer::NS . ':Issuer':
                return Issuer::fromXML($xml);
            case BaseID::NS . ':BaseID':
                $xsiType = $xml->getAttributeNS(Constants::NS_XSI, 'type');
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
