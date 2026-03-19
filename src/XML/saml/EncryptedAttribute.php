<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;

/**
 * Class handling encrypted attributes.
 *
 * @package simplesamlphp/saml2
 */
final class EncryptedAttribute extends AbstractEncryptedElement implements
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * @inheritDoc
     *
     * @return \SimpleSAML\SAML2\XML\saml\Attribute
     */
    public function decrypt(EncryptionAlgorithmInterface $decryptor): Attribute
    {
        return Attribute::fromXML(
            DOMDocumentFactory::fromString($this->decryptData($decryptor))->documentElement,
        );
    }
}
