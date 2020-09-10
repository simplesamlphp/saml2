<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;

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
    protected ?string $use = null;

    /**
     * The KeyInfo for this key.
     *
     * @var \SimpleSAML\XMLSecurity\XML\ds\KeyInfo
     */
    protected KeyInfo $KeyInfo;

    /**
     * Supported EncryptionMethods.
     *
     * @var \SimpleSAML\SAML2\XML\md\EncryptionMethod[]
     */
    protected array $EncryptionMethods = [];


    /**
     * KeyDescriptor constructor.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\KeyInfo $keyInfo
     * @param string|null $use
     * @param \SimpleSAML\SAML2\XML\md\EncryptionMethod[] $encryptionMethod
     */
    public function __construct(
        KeyInfo $keyInfo,
        ?string $use = null,
        array $encryptionMethod = []
    ) {
        $this->setKeyInfo($keyInfo);
        $this->setUse($use);
        $this->setEncryptionMethods($encryptionMethod);
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
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setUse(?string $use): void
    {
        Assert::nullOrOneOf(
            $use,
            ['encryption', 'signing'],
            'The "use" attribute of a KeyDescriptor can only be "encryption" or "signing".'
        );
        $this->use = $use;
    }


    /**
     * Collect the value of the KeyInfo property.
     *
     * @return \SimpleSAML\XMLSecurity\XML\ds\KeyInfo
     */
    public function getKeyInfo(): KeyInfo
    {
        return $this->KeyInfo;
    }


    /**
     * Set the value of the KeyInfo property.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\KeyInfo $keyInfo
     */
    protected function setKeyInfo(KeyInfo $keyInfo): void
    {
        $this->KeyInfo = $keyInfo;
    }


    /**
     * Collect the value of the EncryptionMethod property.
     *
     * @return \SimpleSAML\SAML2\XML\md\EncryptionMethod[]
     */
    public function getEncryptionMethods(): array
    {
        return $this->EncryptionMethods;
    }


    /**
     * Set the value of the EncryptionMethod property.
     *
     * @param \SimpleSAML\SAML2\XML\md\EncryptionMethod[] $encryptionMethods
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setEncryptionMethods(array $encryptionMethods): void
    {
        Assert::allIsInstanceOf($encryptionMethods, EncryptionMethod::class);
        $this->EncryptionMethods = $encryptionMethods;
    }


    /**
     * Initialize an KeyDescriptor.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return \SimpleSAML\SAML2\XML\md\KeyDescriptor
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingElementException if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'KeyDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, KeyDescriptor::NS, InvalidDOMElementException::class);

        $keyInfoElements = KeyInfo::getChildrenOfClass($xml);
        Assert::minCount($keyInfoElements, 1, 'No ds:KeyInfo in the KeyDescriptor.', MissingElementException::class);
        Assert::maxCount($keyInfoElements, 1, 'More than one ds:KeyInfo in the KeyDescriptor.', TooManyElementsException::class);

        return new self(
            $keyInfoElements[0],
            self::getAttribute($xml, 'use', null),
            EncryptionMethod::getChildrenOfClass($xml)
        );
    }


    /**
     * Convert this KeyDescriptor to XML.
     *
     * @param \DOMElement|null $parent The element we should append this KeyDescriptor to.
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
