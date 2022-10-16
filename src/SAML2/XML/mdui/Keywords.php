<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function array_key_first;
use function explode;
use function implode;

/**
 * Class for handling the Keywords metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class Keywords extends AbstractMduiElement
{
    /**
     * The keywords of this item.
     *
     * Array of strings.
     *
     * @var string[]
     */
    protected array $Keywords = [];

    /**
     * The language of this item.
     *
     * @var string
     */
    protected string $lang;


    /**
     * Initialize a Keywords.
     *
     * @param string $lang
     * @param string[] $Keywords
     */
    public function __construct(string $lang, array $Keywords = [])
    {
        $this->setLanguage($lang);
        $this->setKeywords($Keywords);
    }


    /**
     * Collect the value of the lang-property
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->lang;
    }


    /**
     * Set the value of the lang-property
     *
     * @param string $lang
     */
    private function setLanguage(string $lang): void
    {
        $this->lang = $lang;
    }


    /**
     * Collect the value of the Keywords-property
     *
     * @return string[]
     */
    public function getKeywords(): array
    {
        return $this->Keywords;
    }


    /**
     * Set the value of the Keywords-property
     *
     * @param string[] $keywords
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if one of the keywords contains `+`
     */
    private function setKeywords(array $keywords): void
    {
        Assert::allNotContains($keywords, '+', 'Keywords may not contain a "+" character.');
        $this->Keywords = $keywords;
    }


    /**
     * Add the value to the Keywords-property
     *
     * @param string $keyword
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if the keyword contains a `+`
     */
    public function addKeyword(string $keyword): void
    {
        Assert::notContains($keyword, '+', 'Keyword may not contain a "+" character.');
        $this->Keywords[] = $keyword;
    }


    /**
     * Convert XML into a Keywords
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Keywords', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Keywords::NS, InvalidDOMElementException::class);
        Assert::stringNotEmpty($xml->textContent, 'Missing value for Keywords.');

        /** @var string $lang */
        $lang = self::getAttribute($xml, 'xml:lang');

        $Keywords = explode('+', $xml->textContent);

        return new static($lang, $Keywords);
    }


    /**
     * Convert this Keywords to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Keywords to.
     * @throws \Exception
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('xml:lang', $this->getLanguage());
        $e->textContent = rtrim(implode('+', $this->getKeywords()));

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): static
    {
        Assert::notEmpty($data);
        Assert::count($data, 1);

        $lang = array_key_first($data);
        $keywords = $data[$lang];

        return new static($lang, $keywords);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        return [$this->lang => $this->Keywords];
    }
}
