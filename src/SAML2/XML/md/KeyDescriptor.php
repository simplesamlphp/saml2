<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use Exception;
use SAML2\Utils;
use SAML2\XML\Chunk;
use SAML2\XML\ds\KeyInfo;
use Webmozart\Assert\Assert;

/**
 * Class representing a KeyDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
final class KeyDescriptor extends AbstractMdElement
{
    /**
     * What this key can be used for.
     *
     * One of 'encryption', 'signing' or null.
     *
     * @var string|null
     */
    protected $use = null;

    /**
     * The KeyInfo for this key.
     *
     * @var \SAML2\XML\ds\KeyInfo
     */
    protected $KeyInfo;

    /**
     * Supported EncryptionMethods.
     *
     * @var \SAML2\XML\md\EncryptionMethod[]
     */
    protected $EncryptionMethods = [];


    /**
     * KeyDescriptor constructor.
     *
     * @param \SAML2\XML\ds\KeyInfo     $keyInfo
     * @param string|null $use
     * @param \SAML2\XML\md\EncryptionMethod[]|null  $encryptionMethod
     * @throws \InvalidArgumentException
     */
    public function __construct(
        KeyInfo $keyInfo,
        ?string $use = null,
        ?array $encryptionMethod = null
    ) {
        $this->KeyInfo = $keyInfo;
        $this->setUse($use);
        $this->setEncryptionMethods($encryptionMethod);
    }


    /**
     * Initialize an KeyDescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @return \SAML2\XML\md\KeyDescriptor
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        $use = null;
        if ($xml->hasAttribute('use')) {
            $use = $xml->getAttribute('use');
        }

        $keyInfo = Utils::xpQuery($xml, './ds:KeyInfo');
        if (count($keyInfo) > 1) {
            throw new Exception('More than one ds:KeyInfo in the KeyDescriptor.');
        } elseif (empty($keyInfo)) {
            throw new Exception('No ds:KeyInfo in the KeyDescriptor.');
        }
        /**
         * @var \DOMElement $keyInfo[0]
         * @var \SAML2\XML\ds\KeyInfo $keyInfo
         */
        $keyInfo = KeyInfo::fromXML($keyInfo[0]);

        $encmethod = [];
        /** @var \DOMElement $em */
        foreach (Utils::xpQuery($xml, './saml_metadata:EncryptionMethod') as $em) {
            $encmethod[] = new Chunk($em);
        }

        return new self(
            $keyInfo,
            self::getAttribute($xml, 'use', null),
            EncryptionMethod::getChildrenOfClass($xml)
        );
    }


    /**
     * Collect the value of the use property.
     *
     * @return string|null
     */
    public function getUse(): ?string
    {
        return $this->use;
    }


    /**
     * Set the value of the use property.
     *
     * @param string|null $use
     * @throws \InvalidArgumentException
     */
    protected function setUse(?string $use): void
    {
        Assert::nullOrOneOf($use, ['encryption', 'signing']);
        $this->use = $use;
    }


    /**
     * Collect the value of the KeyInfo property.
     *
     * @return \SAML2\XML\ds\KeyInfo
     */
    public function getKeyInfo(): KeyInfo
    {
        return $this->KeyInfo;
    }


    /**
     * Set the value of the KeyInfo property.
     *
     * @param \SAML2\XML\ds\KeyInfo $keyInfo
     */
    protected function setKeyInfo(KeyInfo $keyInfo): void
    {
        $this->KeyInfo = $keyInfo;
    }


    /**
     * Collect the value of the EncryptionMethod property.
     *
     * @return \SAML2\XML\md\EncryptionMethod[]
     */
    public function getEncryptionMethods(): array
    {
        return $this->EncryptionMethods;
    }


    /**
     * Set the value of the EncryptionMethod property.
     *
     * @param \SAML2\XML\md\EncryptionMethod[]|null $encryptionMethods
     * @throws \InvalidArgumentException
     */
    protected function setEncryptionMethods(?array $encryptionMethods): void
    {
        if ($encryptionMethods !== null) {
            Assert::allIsInstanceOf($encryptionMethods, EncryptionMethod::class);
            $this->EncryptionMethods = $encryptionMethods;
        }
    }


    /**
     * Convert this KeyDescriptor to XML.
     *
     * @param \DOMElement $parent The element we should append this KeyDescriptor to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->use !== null) {
            $e->setAttribute('use', $this->use);
        }

        $this->KeyInfo->toXML($e);

        foreach ($this->EncryptionMethods as $em) {
            $em->toXML($e);
        }

        return $e;
    }
}
