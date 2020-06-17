<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\XML\EncryptedElementInterface;
use SAML2\XML\EncryptedElementTrait;
use SAML2\Utils;
use SAML2\XML\AbstractXMLElement;

/**
 * Class handling encrypted assertions.
 *
 * @package SimpleSAMLphp
 */
class EncryptedAssertion extends AbstractSamlElement implements EncryptedElementInterface
{
    use EncryptedElementTrait;

    /**
     * @var bool
     */
    protected $wasSignedAtConstruction = false;


    /**
     * @inheritDoc
     *
     * @return \SAML2\XML\saml\Assertion
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
