<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use InvalidArgumentException;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\EncryptedElementInterface;
use SimpleSAML\SAML2\XML\EncryptedElementTrait;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\AbstractXMLElement;

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
     * @return \SimpleSAML\XML\saml\IdentifierInterface
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
