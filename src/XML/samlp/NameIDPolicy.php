<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

use function array_change_key_case;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function var_export;

/**
 * Class for handling SAML2 NameIDPolicy.
 *
 * @package simplesamlphp/saml2
 */
final class NameIDPolicy extends AbstractSamlpElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;

    /**
     * Initialize a NameIDPolicy.
     *
     * @param string|null $Format
     * @param string|null $SPNameQualifier
     * @param bool|null $AllowCreate
     */
    public function __construct(
        protected ?string $Format = null,
        protected ?string $SPNameQualifier = null,
        protected ?bool $AllowCreate = null,
    ) {
        SAMLAssert::nullOrValidURI($Format);
        Assert::nullOrNotWhitespaceOnly($SPNameQualifier);
    }


    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->Format;
    }


    /**
     * @return string|null
     */
    public function getSPNameQualifier(): ?string
    {
        return $this->SPNameQualifier;
    }


    /**
     * @return bool|null
     */
    public function getAllowCreate(): ?bool
    {
        return $this->AllowCreate;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return empty($this->Format)
            && empty($this->SPNameQualifier)
            && empty($this->AllowCreate);
    }


    /**
     * Convert XML into a NameIDPolicy
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'NameIDPolicy', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, NameIDPolicy::NS, InvalidDOMElementException::class);

        $Format = self::getOptionalAttribute($xml, 'Format', null);
        $SPNameQualifier = self::getOptionalAttribute($xml, 'SPNameQualifier', null);
        $AllowCreate = self::getOptionalBooleanAttribute($xml, 'AllowCreate', null);

        return new static(
            $Format,
            $SPNameQualifier,
            $AllowCreate,
        );
    }


    /**
     * Convert this NameIDPolicy to XML.
     *
     * @param \DOMElement|null $parent The element we should append this NameIDPolicy to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getFormat()) {
            $e->setAttribute('Format', $this->getFormat());
        }

        if ($this->getSPNameQualifier()) {
            $e->setAttribute('SPNameQualifier', $this->getSPNameQualifier());
        }

        if ($this->getAllowCreate() !== null) {
            $e->setAttribute('AllowCreate', var_export($this->getAllowCreate(), true));
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

        return new static(
            $data['Format'] ?? null,
            $data['SPNameQualifier'] ?? null,
            $data['AllowCreate'] ?? null,
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
                'format',
                'spnamequalifier',
                'allowcreate',
            ],
            ArrayValidationException::class,
        );

        Assert::string($data['format'], ArrayValidationException::class);

        $retval = ['Format' => $data['format']];

        if (array_key_exists('spnamequalifier', $data)) {
            Assert::string($data['spnamequalifier'], ArrayValidationException::class);
            $retval['SPNameQualifier'] = $data['spnamequalifier'];
        }

        if (array_key_exists('allowcreate', $data)) {
            Assert::boolean($data['allowcreate'], ArrayValidationException::class);
            $retval['AllowCreate'] = $data['allowcreate'];
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
            'Format' => $this->getFormat(),
            'SPNameQualifier' => $this->getSPNameQualifier(),
            'AllowCreate' => $this->getAllowCreate(),
        ];

        return array_filter($data);
    }
}
