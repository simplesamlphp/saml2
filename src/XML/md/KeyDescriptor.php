<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\KeyTypesValue;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XMLSchema\Exception\{InvalidDOMElementException, MissingElementException, TooManyElementsException};
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;

use function array_pop;

/**
 * Class representing a KeyDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
final class KeyDescriptor extends AbstractMdElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * KeyDescriptor constructor.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\KeyInfo $keyInfo
     * @param \SimpleSAML\SAML2\Type\KeyTypesValue|null $use
     * @param \SimpleSAML\SAML2\XML\md\EncryptionMethod[] $encryptionMethod
     */
    public function __construct(
        protected KeyInfo $keyInfo,
        protected ?KeyTypesValue $use = null,
        protected array $encryptionMethod = [],
    ) {
        Assert::maxCount($encryptionMethod, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($encryptionMethod, EncryptionMethod::class);
    }


    /**
     * Collect the value of the use property.
     *
     * @return \SimpleSAML\SAML2\Type\KeyTypesValue|null
     */
    public function getUse(): ?KeyTypesValue
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
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'KeyDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, KeyDescriptor::NS, InvalidDOMElementException::class);

        $keyInfo = KeyInfo::getChildrenOfClass($xml);
        Assert::minCount($keyInfo, 1, 'No ds:KeyInfo in the KeyDescriptor.', MissingElementException::class);
        Assert::maxCount($keyInfo, 1, 'Too many ds:KeyInfo in the KeyDescriptor.', TooManyElementsException::class);

        return new static(
            array_pop($keyInfo),
            self::getOptionalAttribute($xml, 'use', KeyTypesValue::class, null),
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
            $e->setAttribute('use', $this->getUse()->getValue());
        }

        $this->getKeyInfo()->toXML($e);

        foreach ($this->getEncryptionMethod() as $em) {
            $em->toXML($e);
        }

        return $e;
    }
}
