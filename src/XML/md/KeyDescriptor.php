<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Constants as C;
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
     * KeyDescriptor constructor.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\KeyInfo $keyInfo
     * @param string|null $use
     * @param \SimpleSAML\SAML2\XML\md\EncryptionMethod[] $encryptionMethod
     */
    public function __construct(
        protected KeyInfo $keyInfo,
        protected ?string $use = null,
        protected array $encryptionMethod = [],
    ) {
        Assert::nullOrOneOf(
            $use,
            ['encryption', 'signing'],
            'The "use" attribute of a KeyDescriptor can only be "encryption" or "signing".',
        );
        Assert::maxCount($encryptionMethod, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($encryptionMethod, EncryptionMethod::class);
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
     * Collect the value of the KeyInfo property.
     *
     * @return \SimpleSAML\XMLSecurity\XML\ds\KeyInfo
     */
    public function getKeyInfo(): KeyInfo
    {
        return $this->keyInfo;
    }


    /**
     * Collect the value of the EncryptionMethod property.
     *
     * @return \SimpleSAML\SAML2\XML\md\EncryptionMethod[]
     */
    public function getEncryptionMethod(): array
    {
        return $this->encryptionMethod;
    }


    /**
     * Initialize an KeyDescriptor.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'KeyDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, KeyDescriptor::NS, InvalidDOMElementException::class);

        $keyInfoElements = KeyInfo::getChildrenOfClass($xml);
        Assert::minCount(
            $keyInfoElements,
            1,
            'No ds:KeyInfo in the KeyDescriptor.',
            MissingElementException::class,
        );
        Assert::maxCount(
            $keyInfoElements,
            1,
            'More than one ds:KeyInfo in the KeyDescriptor.',
            TooManyElementsException::class,
        );

        return new static(
            $keyInfoElements[0],
            self::getOptionalAttribute($xml, 'use', null),
            EncryptionMethod::getChildrenOfClass($xml),
        );
    }


    /**
     * Convert this KeyDescriptor to XML.
     *
     * @param \DOMElement|null $parent The element we should append this KeyDescriptor to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getUse() !== null) {
            $e->setAttribute('use', $this->getUse());
        }

        $this->getKeyInfo()->toXML($e);

        foreach ($this->getEncryptionMethod() as $em) {
            $em->toXML($e);
        }

        return $e;
    }
}
