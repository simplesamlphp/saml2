<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use InvalidArgumentException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\XMLStringElementTrait;

use function filter_var;
use function strval;
use function substr;
use function trim;

/**
 * Class for handling the Logo metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class Logo extends AbstractMduiElement
{
    use XMLStringElementTrait;

    /**
     * The width of this logo.
     *
     * @var int
     */
    protected int $width;

    /**
     * The height of this logo.
     *
     * @var int
     */
    protected int $height;

    /**
     * The language of this item.
     *
     * @var string|null
     */
    protected ?string $lang = null;


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
        $this->setContent($url);
        $this->setHeight($height);
        $this->setWidth($width);
        $this->setLanguage($lang);
    }


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \InvalidArgumentException on failure
     * @return void
     */
    protected function validateContent(string $content): void
    {
        // NOTE:  we override the validateContent from the trait to be able to be less restrictive
        // Assert::validURI($content, SchemaViolationException::class); // Covers the empty string
        if (!filter_var(trim($content), FILTER_VALIDATE_URL) && substr(trim($content), 0, 5) !== 'data:') {
            throw new InvalidArgumentException('mdui:Logo is not a valid URL.');
        }
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
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Logo', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Logo::NS, InvalidDOMElementException::class);
        Assert::stringNotEmpty($xml->textContent, 'Missing url value for Logo.');

        $Url = $xml->textContent;
        /** @var int $Width */
        $Width = self::getIntegerAttribute($xml, 'width');
        /** @var int $Height */
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
        $e->textContent = $this->content;
        $e->setAttribute('height', strval($this->height));
        $e->setAttribute('width', strval($this->width));

        if ($this->lang !== null) {
            $e->setAttribute('xml:lang', $this->lang);
        }

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
        Assert::keyExists($data, 'url');

        $Url = $data['url'];
        $Width = $data['width'] ?? null;
        $Height = $data['height'] ?? null;
        $lang = $data['lang'] ?? null;

        return new self($Url, $Height, $Width, $lang);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        return ['url' => $this->content, 'width' => $this->width, 'height' => $this->height, 'lang' => $this->lang];
    }
}
