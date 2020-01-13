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
        $this->setKeywords(
            empty($this->Keywords) ? [$keyword] : array_merge($this->Keywords, [$keyword])
        );
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
        } elseif (!strlen($xml->textContent)) {
            throw new \Exception('Missing value for Keywords.');
        }
        $lang = $xml->getAttribute('xml:lang');

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
        if (!empty($this->Keywords)) {
            foreach ($this->Keywords as $keyword) {
                if (strpos($keyword, "+") !== false) {
                    throw new \Exception('Keywords may not contain a "+" character.');
                }
                $value .= str_replace(' ', '+', $keyword) . ' ';
            }
        }

        $e->appendChild($e->ownerDocument->createTextNode(rtrim($value)));

        return $e;
    }
}
