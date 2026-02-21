<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use DOMElement;

/**
 * Class for handling the Logo metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package SimpleSAMLphp
 */
class Logo
{
    /**
     * The url of this logo.
     *
     * @var string
     */
    private $url;

    /**
     * The width of this logo.
     *
     * @var int
     */
    private $width;

    /**
     * The height of this logo.
     *
     * @var int
     */
    private $height;

    /**
     * The language of this item.
     *
     * @var string|null
     */
    private $lang = null;


    /**
     * Initialize a Logo.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(?DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('width')) {
            throw new \Exception('Missing width of Logo.');
        }
        if (!$xml->hasAttribute('height')) {
            throw new \Exception('Missing height of Logo.');
        }
        if (!strlen($xml->textContent)) {
            throw new \Exception('Missing url value for Logo.');
        }
        $this->setUrl($xml->textContent);
        $this->setWidth(intval($xml->getAttribute('width')));
        $this->setHeight(intval($xml->getAttribute('height')));
        if ($xml->hasAttribute('xml:lang')) {
            $this->setLanguage($xml->getAttribute('xml:lang'));
        }
    }


    /**
     * Collect the value of the url-property
     */
    public function getUrl(): string
    {
        return $this->url;
    }


    /**
     * Set the value of the url-property
     */
    public function setUrl(string $url): void
    {
        if (!filter_var(trim($url), FILTER_VALIDATE_URL) && substr(trim($url), 0, 5) !== 'data:') {
            throw new \InvalidArgumentException('mdui:Logo is not a valid URL.');
        }
        $this->url = $url;
    }


    /**
     * Collect the value of the lang-property
     */
    public function getLanguage(): ?string
    {
        return $this->lang;
    }


    /**
     * Set the value of the lang-property
     */
    public function setLanguage(string $lang): void
    {
        $this->lang = $lang;
    }


    /**
     * Collect the value of the height-property
     */
    public function getHeight(): int
    {
        return $this->height;
    }


    /**
     * Set the value of the height-property
     */
    public function setHeight(int $height): void
    {
        $this->height = $height;
    }


    /**
     * Collect the value of the width-property
     */
    public function getWidth(): int
    {
        return $this->width;
    }


    /**
     * Set the value of the width-property
     */
    public function setWidth(int $width): void
    {
        $this->width = $width;
    }


    /**
     * Convert this Logo to XML.
     *
     * @param \DOMElement $parent The element we should append this Logo to.
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Common::NS, 'mdui:Logo');
        $e->appendChild($doc->createTextNode($this->url));
        $e->setAttribute('width', strval($this->width));
        $e->setAttribute('height', strval($this->height));
        if ($this->lang !== null) {
            $e->setAttribute('xml:lang', $this->lang);
        }
        $parent->appendChild($e);

        return $e;
    }
}
