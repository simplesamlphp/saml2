<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use DOMElement;
use Webmozart\Assert\Assert;

/**
 * Class for handling the Logo metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package SimpleSAMLphp
 */
final class Logo extends AbstractMduiElement
{
    /**
     * The url of this logo.
     *
     * @var string
     */
    protected $url;

    /**
     * The width of this logo.
     *
     * @var int
     */
    protected $width;

    /**
     * The height of this logo.
     *
     * @var int
     */
    protected $height;

    /**
     * The language of this item.
     *
     * @var string|null
     */
    protected $lang = null;


    /**
     * Initialize a Logo.
     *
     * @param string $url
     * @param int $height
     * @param int $width
     * @param string|null $lang
     */
    public function __construct($url, $height, $width, $lang = null)
    {
        $this->setUrl($url);
        $this->setHeight($height);
        $this->setWidth($width);
        $this->setLanguage($lang);
    }


    /**
     * Collect the value of the url-property
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }


    /**
     * Set the value of the url-property
     *
     * @param string $url
     * @return void
     */
    private function setUrl(string $url): void
    {
        if (!filter_var(trim($url), FILTER_VALIDATE_URL) && substr(trim($url), 0, 5) !== 'data:') {
            throw new \InvalidArgumentException('mdui:Logo is not a valid URL.');
        }
        $this->url = $url;
    }


    /**
     * Collect the value of the lang-property
     *
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->lang;
    }


    /**
     * Set the value of the lang-property
     *
     * @param string|null $lang
     * @return void
     */
    private function setLanguage(?string $lang): void
    {
        $this->lang = $lang;
    }


    /**
     * Collect the value of the height-property
     *
     * @return int
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getHeight(): int
    {
        return $this->height;
    }


    /**
     * Set the value of the height-property
     *
     * @param int $height
     * @return void
     */
    private function setHeight(int $height): void
    {
        $this->height = $height;
    }


    /**
     * Collect the value of the width-property
     *
     * @return int
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getWidth(): int
    {
        return $this->width;
    }


    /**
     * Set the value of the width-property
     *
     * @param int $width
     * @return void
     */
    private function setWidth(int $width): void
    {
        $this->width = $width;
    }


    /**
     * Convert XML into a Logo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        if (!$xml->hasAttribute('width')) {
            throw new \Exception('Missing width of Logo.');
        } elseif (!$xml->hasAttribute('height')) {
            throw new \Exception('Missing height of Logo.');
        } elseif (!strlen($xml->textContent)) {
            throw new \Exception('Missing url value for Logo.');
        }

        $Url = $xml->textContent;
        $Width = intval($xml->getAttribute('width'));
        $Height = intval($xml->getAttribute('height'));
        $lang = $xml->hasAttribute('xml:lang') ? $xml->getAttribute('xml:lang') : null;

        return new self($Url, $Width, $Height, $lang);
    }


    /**
     * Convert this Logo to XML.
     *
     * @param \DOMElement $parent The element we should append this Logo to.
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->url);
        Assert::notEmpty($this->width);
        Assert::notEmpty($this->height);

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Logo::NS, 'mdui:Logo');
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
