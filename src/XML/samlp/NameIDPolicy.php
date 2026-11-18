<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Type\BooleanValue;

use function array_change_key_case;
use function array_filter;
use function array_key_exists;
use function array_keys;

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
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $Format
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $SPNameQualifier
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue|null $AllowCreate
     */
    public function __construct(
        protected ?SAMLAnyURIValue $Format = null,
        protected ?SAMLStringValue $SPNameQualifier = null,
        protected ?BooleanValue $AllowCreate = null,
    ) {
        if (
            $AllowCreate !== null
            && $Format !== null
            && $AllowCreate->equals(BooleanValue::fromBoolean(true))
        ) {
            // Per Errata E14: AllowCreate
            Assert::notSame(
                $Format->getValue(),
                C::NAMEID_TRANSIENT,
                sprintf(
                    'AllowCreate=\"true\" MUST NOT be used in conjunction with the %s <NameID> Format.',
                    C::NAMEID_TRANSIENT,
                ),
                ProtocolViolationException::class,
            );
        }
    }


    /**
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null
     */
    public function getFormat(): ?SAMLAnyURIValue
    {
        return $this->Format;
    }


    /**
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getSPNameQualifier(): ?SAMLStringValue
    {
        return $this->SPNameQualifier;
    }


    /**
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null
     */
    public function getAllowCreate(): ?BooleanValue
    {
        return $this->AllowCreate;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     */
    public function isEmptyElement(): bool
    {
        return empty($this->getFormat())
            && empty($this->getSPNameQualifier())
            && empty($this->getAllowCreate());
    }


    /**
     * Convert XML into a NameIDPolicy
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'NameIDPolicy', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, NameIDPolicy::NS, InvalidDOMElementException::class);

        $Format = self::getOptionalAttribute($xml, 'Format', SAMLAnyURIValue::class, null);
        $SPNameQualifier = self::getOptionalAttribute($xml, 'SPNameQualifier', SAMLStringValue::class, null);
        $AllowCreate = self::getOptionalAttribute($xml, 'AllowCreate', BooleanValue::class, null);

        return new static(
            $Format,
            $SPNameQualifier,
            $AllowCreate,
        );
    }


    /**
     * Convert this NameIDPolicy to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getFormat() !== null) {
            $e->setAttribute('Format', $this->getFormat()->getValue());
        }

        if ($this->getSPNameQualifier() !== null) {
            $e->setAttribute('SPNameQualifier', $this->getSPNameQualifier()->getValue());
        }

        if ($this->getAllowCreate() !== null) {
            $e->setAttribute('AllowCreate', $this->getAllowCreate()->getValue());
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array{
     *   'Format'?: string,
     *   'SPNameQualifier'?: string,
     *   'AllowCreate'?: string,
     * } $data
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            $data['Format'] !== null ? SAMLAnyURIValue::fromString($data['Format']) : null,
            $data['SPNameQualifier'] !== null ? SAMLStringValue::fromString($data['SPNameQualifier']) : null,
            $data['AllowCreate'] !== null ? BooleanValue::fromBoolean($data['AllowCreate']) : null,
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array{
     *   'Format'?: string,
     *   'SPNameQualifier'?: string,
     *   'AllowCreate'?: string,
     * } $data
     * @return array{
     *   'Format'?: string,
     *   'SPNameQualifier'?: string,
     *   'AllowCreate'?: string,
     * }
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
     * @return array{
     *   'Format'?: string,
     *   'SPNameQualifier'?: string,
     *   'AllowCreate'?: string,
     * }
     */
    public function toArray(): array
    {
        $data = [
            'Format' => $this->getFormat()?->getValue(),
            'SPNameQualifier' => $this->getSPNameQualifier()?->getValue(),
            'AllowCreate' => $this->getAllowCreate()?->toBoolean(),
        ];

        return array_filter(
            $data,
            function ($v, $k) {
                return $v !== null;
            },
            ARRAY_FILTER_USE_BOTH,
        );
    }
}
