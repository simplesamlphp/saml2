<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use InvalidArgumentException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\XMLStringElementTrait;

use function array_key_first;

/**
 * Abstract class implementing LocalizedNameType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractLocalizedName extends AbstractMdElement
{
    use XMLStringElementTrait;

    /**
     * The root XML namespace.
     */
    public const XML_NS = 'http://www.w3.org/XML/1998/namespace';

    /**
     * The language this string is on.
     *
     * @var string
     */
    protected string $language;


    /**
     * LocalizedNameType constructor.
     *
     * @param string $language The language this string is localized in.
     * @param string $value The localized string.
     */
    final public function __construct(string $language, string $value)
    {
        $this->setLanguage($language);
        $this->setContent($value);
    }


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(/** @scrutinizer ignore-unused */ string $content): void
    {
        Assert::notEmpty($content);
    }


    /**
     * Get the language this string is localized in.
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }


    /**
     * Set the language this string is localized in.
     *
     * @param string $language
     */
    protected function setLanguage(string $language): void
    {
        Assert::notEmpty($language, 'xml:lang cannot be empty.');
        $this->language = $language;
    }


    /**
     * Create an instance of this object from its XML representation.
     *
     * @param \DOMElement $xml
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, static::getLocalName(), InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, static::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(self::XML_NS, 'lang'),
            'Missing xml:lang from ' . static::getLocalName()
        );

        return new static($xml->getAttributeNS(self::XML_NS, 'lang'), $xml->textContent);
    }


    /**
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    final public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttributeNS(self::XML_NS, 'xml:lang', $this->language);
        $e->textContent = $this->getContent();

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): object
    {
        $lang = array_key_first($data);
        $value = $data[$lang];

        return new static($lang, $value);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        return [$this->language => $this->getContent()];
    }
}
