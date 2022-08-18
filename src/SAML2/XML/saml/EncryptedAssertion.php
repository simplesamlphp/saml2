<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmInterface;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\Utils\Security;
use SimpleSAML\XMLSecurity\XML\EncryptedElementInterface;
use SimpleSAML\XMLSecurity\XML\EncryptedElementTrait;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

/**
 * Class handling encrypted assertions.
 *
 * @package simplesamlphp/saml2
 */
class EncryptedAssertion extends AbstractSamlElement implements EncryptedElementInterface
{
    use EncryptedElementTrait;

    /** @var bool */
    protected bool $wasSignedAtConstruction = false;


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
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     * @throws \Exception
     */
    public function decrypt(EncryptionAlgorithmInterface $decryptor): Assertion
    {
        return Assertion::fromXML(
            DOMDocumentFactory::fromString($this->decryptData($decryptor))->documentElement
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
