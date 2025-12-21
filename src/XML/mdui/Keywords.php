<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\ListOfStringsValue;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\TypedTextContentTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Type\LanguageValue;

use function array_key_first;

/**
 * Class for handling the Keywords metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class Keywords extends AbstractMduiElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = ListOfStringsValue::class;


    /**
     * Initialize a Keywords.
     *
     * @param \SimpleSAML\XMLSchema\Type\LanguageValue $lang
     * @param \SimpleSAML\SAML2\Type\ListOfStringsValue $keywords
     */
    public function __construct(
        protected LanguageValue $lang,
        ListOfStringsValue $keywords,
    ) {
        $this->setContent($keywords);
    }


    /**
     * Collect the value of the lang-property
     *
     * @return \SimpleSAML\XMLSchema\Type\LanguageValue
     */
    public function getLanguage(): LanguageValue
    {
        return $this->lang;
    }


    /**
     * Convert XML into a Keywords
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Keywords', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Keywords::NS, InvalidDOMElementException::class);
        Assert::stringNotEmpty($xml->textContent, 'Missing value for Keywords.');

        $lang = self::getAttribute($xml, 'xml:lang', LanguageValue::class);
        $Keywords = ListOfStringsValue::fromString($xml->textContent);

        return new static($lang, $Keywords);
    }


    /**
     * Convert this Keywords to XML.
     *
     * @throws \Exception
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('xml:lang', $this->getLanguage()->getValue());
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
        Assert::notEmpty($data, ArrayValidationException::class);
        Assert::count($data, 1, ArrayValidationException::class);

        $lang = LanguageValue::fromString(array_key_first($data));
        $keywords = $data[$lang->getValue()];

        Assert::allNotContains($keywords, '+', ProtocolViolationException::class);

        return new static($lang, ListOfStringsValue::fromArray($keywords));
    }


    /**
     * Create an array from this class
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        /** @var \SimpleSAML\SAML2\Type\ListOfStringsValue $content */
        $content = $this->getContent();

        return [$this->getLanguage()->getValue() => $content->toArray()];
    }
}
