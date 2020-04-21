<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Utils;
use SAML2\XML\AbstractXMLElement;
use SAML2\XML\EncryptedElementInterface;
use SAML2\XML\EncryptedElementTrait;

/**
 * Class handling encrypted attributes.
 *
 * @package simplesamlphp/saml2
 */
class EncryptedAttribute extends AbstractSamlElement implements EncryptedElementInterface
{
    use EncryptedElementTrait;


    /**
     * @inheritDoc
     *
     * @return \SAML2\XML\saml\Attribute
     * @throws \Exception
     *
     * @psalm-suppress MismatchingDocblockReturnType
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function decrypt(XMLSecurityKey $key, array $blacklist = []): AbstractXMLElement
    {
        $attrXML = Utils::decryptElement($this->encryptedData->toXML(), $key, $blacklist);

        return Attribute::fromXML($attrXML);
    }
}
