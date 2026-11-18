<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\Type\LangValue;
use SimpleSAML\XML\TypedTextContentTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSchema\Type\PositiveIntegerValue;

/**
 * Class for handling the Logo metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class Logo extends AbstractMduiElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = SAMLAnyURIValue::class;


    private static string $scheme_regex = '/^(data|http[s]?[:])/i';


    /**
     * Initialize a Logo.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $url
     * @param \SimpleSAML\XMLSchema\Type\PositiveIntegerValue $height
     * @param \SimpleSAML\XMLSchema\Type\PositiveIntegerValue $width
     * @param \SimpleSAML\XML\Type\LangValue|null $lang
     */
    public function __construct(
        SAMLAnyURIValue $url,
        protected PositiveIntegerValue $height,
        protected PositiveIntegerValue $width,
        protected ?LangValue $lang = null,
    ) {
        $this->setContent($url);
    }


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     *
     * @throws \InvalidArgumentException on failure
     */
    protected function validateContent(string $content): void
    {
        Assert::validURI($content, SchemaViolationException::class);
        Assert::regex(self::$scheme_regex, $content, ProtocolViolationException::class);
    }


    /**
     * Collect the value of the lang-property
     *
     * @return \SimpleSAML\XML\Type\LangValue|null
     */
    public function getLanguage(): ?LangValue
    {
        return $this->lang;
    }


    /**
     * Collect the value of the height-property
     *
     * @return \SimpleSAML\XMLSchema\Type\PositiveIntegerValue
     */
    public function getHeight(): PositiveIntegerValue
    {
        return $this->height;
    }


    /**
     * Collect the value of the width-property
     *
     * @return \SimpleSAML\XMLSchema\Type\PositiveIntegerValue
     */
    public function getWidth(): PositiveIntegerValue
    {
        return $this->width;
    }


    /**
     * Convert XML into a Logo
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Logo', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Logo::NS, InvalidDOMElementException::class);
        Assert::stringNotEmpty($xml->textContent, 'Missing url value for Logo.');

        $Url = SAMLAnyURIValue::fromString($xml->textContent);
        $Width = self::getAttribute($xml, 'width', PositiveIntegerValue::class);
        $Height = self::getAttribute($xml, 'height', PositiveIntegerValue::class);
        $lang = self::getOptionalAttribute($xml, 'xml:lang', LangValue::class, null);

        return new static($Url, $Height, $Width, $lang);
    }


    /**
     * Convert this Logo to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->getContent()->getValue();
        $e->setAttribute('height', $this->getHeight()->getValue());
        $e->setAttribute('width', $this->getWidth()->getValue());

        if ($this->getLanguage() !== null) {
            $e->setAttribute('xml:lang', $this->getLanguage()->getValue());
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array{
     *   'url': string,
     *   'height'?: int,
     *   'width'?: int,
     *   'lang'?: string,
     * } $data
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            SAMLAnyURIValue::fromString($data['url']),
            PositiveIntegerValue::fromInteger($data['height']),
            PositiveIntegerValue::fromInteger($data['width']),
            $data['lang'] !== null ? LangValue::fromString($data['lang']) : null,
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array{
     *   'url': string,
     *   'height'?: int,
     *   'width'?: int,
     *   'lang'?: string,
     * } $data
     * @return array{
     *   'url': string,
     *   'height'?: int,
     *   'width'?: int,
     *   'lang'?: string,
     * }
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
     * @return array{
     *   'url': string,
     *   'height'?: int,
     *   'width'?: int,
     *   'lang'?: string,
     * }
     */
    public function toArray(): array
    {
        $lang = $this->getLanguage()?->getValue();

        return [
            'url' => $this->getContent()->getValue(),
            'width' => $this->getWidth()->toInteger(),
            'height' => $this->getHeight()->toInteger(),
        ] + (isset($lang) ? ['lang' => $lang] : []);
    }
}
