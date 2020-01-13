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
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = null)
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
     *
     * @return string
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getUrl(): string
    {
        Assert::notEmpty($this->url);

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
     * @param string $lang
     * @return void
     */
    private function setLanguage(string $lang): void
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
        Assert::notEmpty($this->height);

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
        Assert::notEmpty($this->width);

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
