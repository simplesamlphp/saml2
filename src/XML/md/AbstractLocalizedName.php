<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\TypedTextContentTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;
use SimpleSAML\XMLSchema\Type\LanguageValue;

use function array_key_first;

/**
 * Abstract class implementing LocalizedNameType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractLocalizedName extends AbstractMdElement implements ArrayizableElementInterface
{
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = SAMLStringValue::class;


    /**
     * LocalizedNameType constructor.
     *
     * @param \SimpleSAML\XMLSchema\Type\LanguageValue $language The language this string is localized in.
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue $content The localized string.
     */
    public function __construct(
        protected LanguageValue $language,
        SAMLStringValue $content,
    ) {
        $this->setContent($content);
    }


    /**
     * Get the language this string is localized in.
     *
     * @return \SimpleSAML\XMLSchema\Type\LanguageValue
     */
    public function getLanguage(): LanguageValue
    {
        return $this->language;
    }


    /**
     * Create an instance of this object from its XML representation.
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, static::getLocalName(), InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, static::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C::NS_XML, 'lang'),
            'Missing xml:lang from ' . static::getLocalName(),
            MissingAttributeException::class,
        );

        return new static(
            LanguageValue::fromString($xml->getAttributeNS(C::NS_XML, 'lang')),
            SAMLStringValue::fromString($xml->textContent),
        );
    }


    /**
     */
    final public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttributeNS(C::NS_XML, 'xml:lang', $this->getLanguage()->getValue());
        $e->textContent = $this->getContent()->getValue();

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array<string, string> $data
     */
    public static function fromArray(array $data): static
    {
        Assert::count($data, 1, ArrayValidationException::class);

        $lang = LanguageValue::fromString(array_key_first($data));
        $value = SAMLStringValue::fromString($data[$lang->getValue()]);

        return new static($lang, $value);
    }


    /**
     * Create an array from this class
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [$this->getLanguage()->getValue() => $this->getContent()->getValue()];
    }
}
