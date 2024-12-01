<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use InvalidArgumentException;
use SimpleSAML\Assert\Assert;
//use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
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
final class Logo extends AbstractMduiElement implements ArrayizableElementInterface
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
        // SAMLAssert::validURI($content, SchemaViolationException::class); // Covers the empty string
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
        $Width = self::getIntegerAttribute($xml, 'width');
        $Height = self::getIntegerAttribute($xml, 'height');
        $lang = self::getOptionalAttribute($xml, 'xml:lang', null);

        return new static($Url, $Height, $Width, $lang);
    }


    /**
     * Convert this Logo to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Logo to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
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
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static($data['url'], $data['height'], $data['width'], $data['lang'] ?? null);
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array $data
     * @return array $data
     */
    private static function processArrayContents(array $data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);

        // Make sure the array keys are known for this kind of object
        Assert::allOneOf(
            array_keys($data),
            [
                'url',
                'height',
                'width',
                'lang',
            ],
            ArrayValidationException::class,
        );

        Assert::keyExists($data, 'url', ArrayValidationException::class);
        Assert::keyExists($data, 'height', ArrayValidationException::class);
        Assert::keyExists($data, 'width', ArrayValidationException::class);

        Assert::integer($data['height'], ArrayValidationException::class);
        Assert::integer($data['width'], ArrayValidationException::class);

        $retval = [
            'url' => $data['url'],
            'height' => $data['height'],
            'width' => $data['width'],
        ];

        if (array_key_exists('lang', $data)) {
            Assert::string($data['lang'], ArrayValidationException::class);
            $retval['lang'] = $data['lang'];
        }

        return $retval;
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        $lang = $this->getLanguage();

        return [
            'url' => $this->getContent(),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
        ] + (isset($lang) ? ['lang' => $lang] : []);
    }
}
