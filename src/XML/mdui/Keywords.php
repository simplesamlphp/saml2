<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\{ArrayValidationException, ProtocolViolationException};
use SimpleSAML\SAML2\Type\ListOfStringsValue;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\Type\LanguageValue;
use SimpleSAML\XML\TypedTextContentTrait;

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

    /** @var string */
    public const TEXTCONTENT_TYPE = ListOfStringsValue::class;



    /**
     * Initialize a Keywords.
     *
     * @param \SimpleSAML\XML\Type\LanguageValue $lang
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
     * @return \SimpleSAML\XML\Type\LanguageValue
     */
    public function getLanguage(): LanguageValue
    {
        return $this->lang;
    }


    /**
     * Convert XML into a Keywords
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
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
     * @param \DOMElement|null $parent The element we should append this Keywords to.
     * @throws \Exception
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('xml:lang', $this->getLanguage()->getValue());
        $e->textContent = $this->getContent()->getValue();

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
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
     * @return array
     */
    public function toArray(): array
    {
        return [$this->getLanguage()->getValue() => $this->getContent()->toArray()];
    }
}
