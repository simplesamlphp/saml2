<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use InvalidArgumentException;
use SimpleSAML\SAML2\XML\EncryptedElementTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\SerializableElementInterface;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;
use SimpleSAML\XMLSecurity\XML\EncryptedElementInterface;

use function implode;

/**
 * Class representing an encrypted identifier.
 *
 * @package simplesamlphp/saml2
 */
class EncryptedID extends AbstractSamlElement implements
    EncryptedElementInterface,
    IdentifierInterface,
    SchemaValidatableElementInterface
{
    use EncryptedElementTrait;
    use SchemaValidatableElementTrait;

    /**
     * @inheritDoc
     *
     * @return \SimpleSAML\XML\SerializableElementInterface
     * @throws \InvalidArgumentException
     */
    public function decrypt(EncryptionAlgorithmInterface $decryptor): SerializableElementInterface
    {
        $xml = DOMDocumentFactory::fromString($this->decryptData($decryptor))->documentElement;

        $id = implode(':', [$xml->namespaceURI, $xml->localName]);
        switch ($id) {
            case NameID::NS . ':NameID':
                return NameID::fromXML($xml);
            case AbstractBaseID::NS . ':BaseID':
                return AbstractBaseID::fromXML($xml);
            default:
                // Fall thru
        }
        throw new InvalidArgumentException('Unknown or unsupported encrypted identifier.');
    }
}
