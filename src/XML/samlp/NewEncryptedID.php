<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use InvalidArgumentException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\AbstractBaseID;
use SimpleSAML\SAML2\XML\saml\AbstractEncryptedElement;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\SerializableElementInterface;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;

use function implode;

/**
 * Class representing an encrypted identifier.
 *
 * @package simplesamlphp/saml2
 */
final class NewEncryptedID extends AbstractEncryptedElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    public const string NS = C::NS_SAMLP;

    public const string NS_PREFIX = 'samlp';

    public const string SCHEMA = 'resources/schemas/saml-schema-protocol-2.0.xsd';


    /**
     * @inheritDoc
     *
     * @return \SimpleSAML\XML\SerializableElementInterface
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
