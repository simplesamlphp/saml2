<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use InvalidArgumentException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\InvalidDOMElementException;

/**
 * Class for handling the Logo metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package simplesamlphp/saml2
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
     * @throws \InvalidArgumentException if the supplied value is not a valid URL
     */
    private function setUrl(string $url): void
    {
        if (!filter_var(trim($url), FILTER_VALIDATE_URL) && substr(trim($url), 0, 5) !== 'data:') {
            throw new InvalidArgumentException('mdui:Logo is not a valid URL.');
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
     *
     * @throws \SimpleSAML\SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\SAML2\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Logo', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Logo::NS, InvalidDOMElementException::class);
        Assert::stringNotEmpty($xml->textContent, 'Missing url value for Logo.');

        $Url = $xml->textContent;
        $Width = self::getIntegerAttribute($xml, 'width');
        $Height = self::getIntegerAttribute($xml, 'height');
        $lang = self::getAttribute($xml, 'xml:lang');

        return new self($Url, $Height, $Width, $lang);
    }


    /**
     * Convert this Logo to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Logo to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = $this->instantiateParentElement($parent);
        $e->appendChild($e->ownerDocument->createTextNode($this->url));
        $e->setAttribute('height', strval($this->height));
        $e->setAttribute('width', strval($this->width));

        if ($this->lang !== null) {
            $e->setAttribute('xml:lang', $this->lang);
        }

        return $e;
    }
}
