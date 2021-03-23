<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use InvalidArgumentException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Abstract class implementing LocalizedNameType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractLocalizedName extends AbstractMdElement
{
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
     * The localized string.
     *
     * @var string
     */
    protected string $value;


    /**
     * LocalizedNameType constructor.
     *
     * @param string $language The language this string is localized in.
     * @param string $value The localized string.
     */
    final public function __construct(string $language, string $value)
    {
        $this->setLanguage($language);
        $this->setValue($value);
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
        $qualifiedName = join('', array_slice(explode('\\', static::class), -1));
        Assert::eq(
            $xml->localName,
            $qualifiedName,
            'Unexpected name for localized name: ' . $xml->localName . '. Expected: ' . $qualifiedName . '.',
            InvalidDOMElementException::class
        );
        Assert::true(
            $xml->hasAttributeNS(self::XML_NS, 'lang'),
            'Missing xml:lang from ' . $qualifiedName
        );

        return new static($xml->getAttributeNS(self::XML_NS, 'lang'), $xml->textContent);
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
     * Get the localized string.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }


    /**
     * Set the localized string.
     *
     * @param string $value
     */
    protected function setValue(string $value): void
    {
        $this->value = $value;
    }


    /**
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    final public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttributeNS(self::XML_NS, 'xml:lang', $this->language);
        $e->textContent = $this->value;

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
        return [$this->language => $this->value];
    }
}
