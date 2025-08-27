<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\XMLSchema\Exception\{InvalidDOMElementException, MissingAttributeException};
use SimpleSAML\XMLSchema\Type\LanguageValue;

use function array_key_first;

/**
 * Abstract class implementing LocalizedURIType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractLocalizedURI extends AbstractLocalizedName
{
    /** @var string */
    public const TEXTCONTENT_TYPE = SAMLAnyURIValue::class;


    /**
     * LocalizedNameType constructor.
     *
     * @param \SimpleSAML\XMLSchema\Type\LanguageValue $language The language this string is localized in.
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $content The localized string.
     */
    final public function __construct(
        protected LanguageValue $language,
        SAMLAnyURIValue $content,
    ) {
        $content = SAMLStringValue::fromString($content->getValue());

        parent::__construct($language, $content);
    }


    /**
     * Create an instance of this object from its XML representation.
     *
     * @param \DOMElement $xml
     * @return static
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
            SAMLAnyURIValue::fromString($xml->textContent),
        );
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::count($data, 1);

        $lang = LanguageValue::fromString(array_key_first($data));
        $value = SAMLAnyURIValue::fromString(
            $data[$lang->getValue()],
        );

        return new static($lang, $value);
    }
}
