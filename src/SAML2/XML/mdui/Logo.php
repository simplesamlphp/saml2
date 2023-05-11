<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use InvalidArgumentException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\StringElementTrait;

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
    use StringElementTrait;


    /**
     * Initialize a Logo.
     *
     * @param string $url
     * @param int $height
     * @param int $width
     * @param string|null $lang
     */
    public function __construct(
        protected string $url,
        protected int $height,
        protected int $width,
        protected ?string $lang = null,
    ) {
        $this->setContent($url);
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
     * Collect the value of the height-property
     *
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
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
     * Convert XML into a Logo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Logo', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Logo::NS, InvalidDOMElementException::class);
        Assert::stringNotEmpty($xml->textContent, 'Missing url value for Logo.');

        $Url = $xml->textContent;
        /** @var int $Width */
        $Width = self::getIntegerAttribute($xml, 'width');
        /** @var int $Height */
        $Height = self::getIntegerAttribute($xml, 'height');
        $lang = self::getAttribute($xml, 'xml:lang', null);

        return new static($Url, $Height, $Width, $lang);
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
        $e->textContent = $this->getContent();
        $e->setAttribute('height', strval($this->getHeight()));
        $e->setAttribute('width', strval($this->getWidth()));

        if ($this->getLanguage() !== null) {
            $e->setAttribute('xml:lang', $this->getLanguage());
        }

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
        Assert::keyExists($data, 'url');

        $Url = $data['url'];
        $Width = $data['width'];
        Assert::notNull(
            $Width,
            'Missing \'width\' attribute on mdui:Logo.',
            MissingAttributeException::class,
        );
        $Height = $data['height'];
        Assert::notNull(
            $Height,
            'Missing \'height\' attribute on mdui:Logo.',
            MissingAttributeException::class,
        );
        $lang = $data['lang'] ?? null;

        return new static($Url, $Height, $Width, $lang);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'url' => $this->getContent(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
            'lang' => $this->getLanguage(),
        ];
    }
}
