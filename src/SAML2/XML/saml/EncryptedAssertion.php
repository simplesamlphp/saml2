<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\XML\EncryptedElementInterface;
use SimpleSAML\SAML2\XML\EncryptedElementTrait;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\AbstractXMLElement;

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


    /**
     * @inheritDoc
     *
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     * @throws \Exception
     *
     * @psalm-suppress MismatchingDocblockReturnType
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function decrypt(XMLSecurityKey $key, array $blacklist = []): AbstractXMLElement
    {
        $assertionXML = Utils::decryptElement($this->encryptedData->toXML(), $key, $blacklist);

        Utils::getContainer()->debugMessage($assertionXML, 'decrypt');

        return Assertion::fromXML($assertionXML);
    }


    /**
     * @return bool
     */
    public function wasSignedAtConstruction(): bool
    {
        return $this->wasSignedAtConstruction;
    }
}
