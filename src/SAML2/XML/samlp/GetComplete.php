<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\StringElementTrait;

use function array_key_first;

/**
 * Class representing a samlp:GetComplete element.
 *
 * @package simplesaml/saml2
 */
final class GetComplete extends AbstractSamlpElement
{
    use StringElementTrait;


    /**
     * @param string $content
     */
    public function __construct(string $content)
    {
        $this->setContent($content);
    }


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(string $content): void
    {
        Assert::validURI($content, SchemaViolationException::class); // Covers the empty string
    }


    /**
     * Convert XML into an GetComplete
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'GetComplete', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, GetComplete::NS, InvalidDOMElementException::class);

        return new static($xml->textContent);
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::allString($data, ArrayValidationException::class);

        $index = array_key_first($data);
        return new static($data[$index]);
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        return [$this->getContent()];
    }
}
