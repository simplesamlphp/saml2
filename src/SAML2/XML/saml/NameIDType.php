<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\XML\Exception\ArrayValidationException;
use SimpleSAML\XML\StringElementTrait;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;

/**
 * SAML NameIDType abstract data type.
 *
 * @package simplesamlphp/saml2
 */

abstract class NameIDType extends AbstractBaseIDType
{
    use StringElementTrait;


    /**
     * Initialize a saml:NameIDType from scratch
     *
     * @param string $value
     * @param string|null $Format
     * @param string|null $SPProvidedID
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    protected function __construct(
        string $value,
        ?string $nameQualifier = null,
        ?string $spNameQualifier = null,
        protected ?string $format = null,
        protected ?string $spProvidedID = null,
    ) {
        Assert::nullOrValidURI($format); // Covers the empty string
        Assert::nullOrNotWhitespaceOnly($spProvidedID);

        parent::__construct($nameQualifier, $spNameQualifier);

        $this->setContent($value);
    }


    /**
     * Collect the value of the Format-property
     *
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }


    /**
     * Collect the value of the SPProvidedID-property
     *
     * @return string|null
     */
    public function getSPProvidedID(): ?string
    {
        return $this->spProvidedID;
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
        Assert::notWhitespaceOnly($content);
    }


    /**
     * Convert this NameIDType to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this NameIDType.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        if ($this->getFormat() !== null) {
            $e->setAttribute('Format', $this->getFormat());
        }

        if ($this->getSPProvidedID() !== null) {
            $e->setAttribute('SPProvidedID', $this->getSPProvidedID());
        }

        $e->textContent = $this->getContent();
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

        return new static(
            $data['value'],
            $data['NameQualifier'] ?? null,
            $data['SPNameQualifier'] ?? null,
            $data['Format'] ?? null,
            $data['SPProvidedID'] ?? null,
        );
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
                'value',
                'format',
                'namequalifier',
                'spnamequalifier',
                'spprovidedid',
            ],
            ArrayValidationException::class,
        );

        Assert::keyExists($data, 'value', ArrayValidationException::class);
        Assert::string($data['value'], ArrayValidationException::class);
        $retval = ['value' => $data['value']];

        if (array_key_exists('format', $data)) {
            Assert::string($data['format'], ArrayValidationException::class);
            $retval['Format'] = $data['format'];
        }

        if (array_key_exists('namequalifier', $data)) {
            Assert::string($data['namequalifier'], ArrayValidationException::class);
            $retval['NameQualifier'] = $data['namequalifier'];
        }

        if (array_key_exists('spnamequalifier', $data)) {
            Assert::string($data['spnamequalifier'], ArrayValidationException::class);
            $retval['SPNameQualifier'] = $data['spnamequalifier'];
        }

        if (array_key_exists('spprovidedid', $data)) {
            Assert::string($data['spprovidedid'], ArrayValidationException::class);
            $retval['SPProvidedID'] = $data['spprovidedid'];
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
        $data = [
            'value' => $this->getContent(),
            'Format' => $this->getFormat(),
            'NameQualifier' => $this->getNameQualifier(),
            'SPNameQualifier' => $this->getSPNameQualifier(),
            'SPProvidedID' => $this->getSPProvidedID(),
        ];

        return array_filter($data);
    }
}
