<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\XML\EncryptedElementTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;
use SimpleSAML\XMLSecurity\XML\EncryptedElementInterface;

/**
 * Class handling encrypted assertions.
 *
 * @package simplesamlphp/saml2
 */
final class EncryptedAssertion extends AbstractSamlElement implements EncryptedElementInterface
{
    use EncryptedElementTrait;

    /** @var bool */
    protected bool $wasSignedAtConstruction = false;


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


    /**
     * @return bool
     */
    public function wasSignedAtConstruction(): bool
    {
        return $this->wasSignedAtConstruction;
    }
}
