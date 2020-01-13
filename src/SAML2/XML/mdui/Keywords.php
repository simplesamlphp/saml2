<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use DOMElement;
use Webmozart\Assert\Assert;

/**
 * Class for handling the Keywords metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package SimpleSAMLphp
 */
final class Keywords extends AbstractMduiElement
{
    /**
     * The keywords of this item.
     *
     * Array of strings.
     *
     * @var string[]|null
     */
    protected $Keywords = null;

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
    public function __construct(string $lang, array $Keywords = null)
    {
        $this->setLanguage($lang);
        $this->setKeywords($Keywords);
    }


    /**
     * Collect the value of the lang-property
     *
     * @return string
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getLanguage(): string
    {
        Assert::notEmpty($this->lang);

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
     * @return string[]|null
     */
    public function getKeywords(): ?array
    {
        return $this->Keywords;
    }


    /**
     * Set the value of the Keywords-property
     *
     * @param string[]|null $keywords
     * @return void
     */
    private function setKeywords(?array $keywords): void
    {
        $this->Keywords = $keywords;
    }


    /**
     * Add the value to the Keywords-property
     *
     * @param string $keyword
     * @return void
     */
    public function addKeyword(string $keyword): void
    {
        $this->setKeywords(empty($this->Keywords) ? [$keyword] : array_merge($this->Keywords, [$keyword]));
    }


    /**
     * Convert XML into a Keywords
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        if (!$xml->hasAttribute('xml:lang')) {
            throw new \Exception('Missing lang on Keywords.');
        }
        $lang = $xml->getAttribute('xml:lang');

        if (!strlen($xml->textContent)) {
            throw new \Exception('Missing value for Keywords.');
        }

        $Keywords = [];
        foreach (explode(' ', $xml->textContent) as $keyword) {
            $Keywords[] = str_replace('+', ' ', $keyword);
        }

        return new self($lang, $Keywords);
    }


    /**
     * Convert this Keywords to XML.
     *
     * @param \DOMElement $parent The element we should append this Keywords to.
     * @throws \Exception
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->lang, "Cannot convert Keywords to XML without a language set.");

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Keywords::NS, 'mdui:Keywords');
        $e->setAttribute('xml:lang', $this->lang);
        $value = '';
        foreach ($this->Keywords as $keyword) {
            if (strpos($keyword, "+") !== false) {
                throw new \Exception('Keywords may not contain a "+" character.');
            }
            $value .= str_replace(' ', '+', $keyword) . ' ';
        }
        $value = rtrim($value);
        $e->appendChild($doc->createTextNode($value));
        $parent->appendChild($e);

        return $e;
    }
}
