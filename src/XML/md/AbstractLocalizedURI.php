<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\Type\LangValue;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;

use function array_key_first;

/**
 * Abstract class implementing LocalizedURIType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractLocalizedURI extends AbstractLocalizedName
{
    public const string TEXTCONTENT_TYPE = SAMLAnyURIValue::class;


    /**
     * LocalizedNameType constructor.
     *
     * @param \SimpleSAML\XML\Type\LangValue $language The language this string is localized in.
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $content The localized string.
     */
    final public function __construct(
        protected LangValue $language,
        SAMLAnyURIValue $content,
    ) {
        $content = SAMLStringValue::fromString($content->getValue());

        parent::__construct($language, $content);
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
            LangValue::fromString($xml->getAttributeNS(C::NS_XML, 'lang')),
            SAMLAnyURIValue::fromString($xml->textContent),
        );
    }


    /**
     * Create a class from an array
     *
     * @param array<string, string> $data
     */
    public static function fromArray(array $data): static
    {
        Assert::count($data, 1);

        $lang = LangValue::fromString(array_key_first($data));
        $value = SAMLAnyURIValue::fromString(
            $data[$lang->getValue()],
        );

        return new static($lang, $value);
    }
}
