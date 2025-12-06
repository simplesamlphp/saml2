<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;

/**
 * Class handling encrypted assertions.
 *
 * @package simplesamlphp/saml2
 */
final class EncryptedAssertion extends AbstractEncryptedElement implements
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * @inheritDoc
     *
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     * @throws \Exception
     */
    public function decrypt(EncryptionAlgorithmInterface $decryptor): Assertion
    {
        return Assertion::fromXML(
            DOMDocumentFactory::fromString($this->decryptData($decryptor))->documentElement,
        );
    }
}
