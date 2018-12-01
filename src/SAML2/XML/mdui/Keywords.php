<?php

namespace SAML2\XML\mdui;

/**
 * Class for handling the Keywords metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package SimpleSAMLphp
 */
class Keywords
{
    /**
     * The keywords of this item.
     *
     * Array of strings.
     *
     * @var string[]
     */
    public $Keywords;

    /**
     * The language of this item.
     *
     * @var string
     */
    public $lang;

    /**
     * Initialize a Keywords.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('xml:lang')) {
            throw new \Exception('Missing lang on Keywords.');
        }
        if (!is_string($xml->textContent) || !strlen($xml->textContent)) {
            throw new \Exception('Missing value for Keywords.');
        }
        $this->setKeywords([]);
        foreach (explode(' ', $xml->textContent) as $keyword) {
            $this->addKeyword(str_replace('+', ' ', $keyword));
        }
        $this->setLanguage($xml->getAttribute('xml:lang'));
    }

    /**
     * Collect the value of the lang-property
     * @return string
     */
    public function getLanguage()
    {
        return $this->lang;
    }

    /**
     * Set the value of the lang-property
     * @param string $lang
     */
    public function setLanguage($lang)
    {
        assert(is_string($lang) || is_null($lang));
        $this->lang = $lang;
    }

    /**
     * Collect the value of the Keywords-property
     * @return string[]
     */
    public function getKeywords()
    {
        return $this->Keywords;
    }

    /**
     * Set the value of the Keywords-property
     * @param string[] $keywords
     */
    public function setKeywords(array $keywords)
    {
        $this->Keywords = $keywords;
    }

    /**
     * Add the value to the Keywords-property
     * @param string $keyword
     */
    public function addKeyword($keyword)
    {
        assert(is_string($keyword));
        $this->Keywords[] = $keyword;
    }

    /**
     * Convert this Keywords to XML.
     *
     * @param \DOMElement $parent The element we should append this Keywords to.
     * @return \DOMElement
     * @throws \Exception
     */
    public function toXML(\DOMElement $parent)
    {
        assert(is_string($this->getLanguage()));
        assert(is_array($this->getKeywords()));

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Common::NS, 'mdui:Keywords');
        $e->setAttribute('xml:lang', $this->getLanguage());
        $value = '';
        foreach ($this->getKeywords() as $keyword) {
            if (strpos($keyword, "+") !== false) {
                throw new \Exception('Keywords may not contain a "+" character.');
            }
            $value .= str_replace(' ', '+', $keyword).' ';
        }
        $value = rtrim($value);
        $e->appendChild($doc->createTextNode($value));
        $parent->appendChild($e);

        return $e;
    }
}
