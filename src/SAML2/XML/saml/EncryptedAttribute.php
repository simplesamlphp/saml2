<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\XML\EncryptedElementTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\XML\EncryptedElementInterface;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;

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
     * @return \SimpleSAML\SAML2\XML\saml\Attribute
     */
    public function decrypt(EncryptionAlgorithmInterface $decryptor): Attribute
    {
        return Attribute::fromXML(
            DOMDocumentFactory::fromString($this->decryptData($decryptor))->documentElement,
        );
    }
}
