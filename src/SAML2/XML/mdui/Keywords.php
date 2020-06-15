<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use DOMElement;
use SAML2\Exception\InvalidDOMElementException;
use SimpleSAML\Assert\Assert;

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
    protected $Keywords = [];

    /**
     * The language of this item.
     *
     * @var string
     */
    protected $lang;


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
     * @return void
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
     * @return void
     *
     * @throws \InvalidArgumentException if one of the keywords contains `+`
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
     * @return void
     *
     * @throws \InvalidArgumentException if the keyword contains a `+`
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
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SAML2\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Keywords', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Keywords::NS, InvalidDOMElementException::class);
        Assert::stringNotEmpty($xml->textContent, 'Missing value for Keywords.');

        $lang = self::getAttribute($xml, 'xml:lang');

        $Keywords = [];
        foreach (explode(' ', $xml->textContent) as $keyword) {
            $Keywords[] = str_replace('+', ' ', $keyword);
        }

        return new self($lang, $Keywords);
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
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('xml:lang', $this->lang);

        $value = '';
        foreach ($this->Keywords as $keyword) {
            $value .= str_replace(' ', '+', $keyword) . ' ';
        }

        $e->appendChild($e->ownerDocument->createTextNode(rtrim($value)));

        return $e;
    }
}
