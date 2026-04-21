<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\shibmd;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\TypedTextContentTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Type\BooleanValue;

use function array_change_key_case;
use function array_key_exists;
use function array_keys;

/**
 * Class which represents the Scope element found in Shibboleth metadata.
 *
 * @link https://wiki.shibboleth.net/confluence/display/SC/ShibMetaExt+V1.0
 * @package simplesamlphp/saml2
 */
final class Scope extends AbstractShibmdElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = SAMLStringValue::class;


    /**
     * Create a Scope.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue $scope
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue|null $regexp
     */
    public function __construct(
        SAMLStringValue $scope,
        protected ?BooleanValue $regexp = null,
    ) {
        $this->setContent($scope);
    }


    /**
     * Collect the value of the regexp-property
     *
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null
     */
    public function isRegexpScope(): ?BooleanValue
    {
        return $this->regexp;
    }


    /**
     * Convert XML into a Scope
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Scope', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Scope::NS, InvalidDOMElementException::class);

        return new static(
            SAMLStringValue::fromString($xml->textContent),
            self::getOptionalAttribute($xml, 'regexp', BooleanValue::class, null),
        );
    }


    /**
     * Convert this Scope to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = strval($this->getContent());

        if ($this->isRegexpScope() !== null) {
            $e->setAttribute('regexp', strval($this->isRegexpScope()));
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array{
     *   'scope': string,
     *   'isRegexpScope'?: boolean,
     * } $data
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            SAMLStringValue::fromString($data['scope']),
            $data['isRegexpScope'] !== null ? BooleanValue::fromBoolean($data['isRegexpScope']) : null,
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array{
     *   'scope': string,
     *   'isRegexpScope'?: boolean,
     * } $data
     * @return array{
     *   'scope': string,
     *   'isRegexpScope'?: boolean,
     * }
     */
    private static function processArrayContents(array $data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);

        // Make sure the array keys are known for this kind of object
        Assert::allOneOf(
            array_keys($data),
            [
                'scope',
                'isregexpscope',
            ],
            ArrayValidationException::class,
        );

        Assert::keyExists($data, 'scope', ArrayValidationException::class);
        Assert::string($data['scope'], ArrayValidationException::class);

        $retval = [
            'scope' => $data['scope'],
        ];

        if (array_key_exists('isregexpscope', $data)) {
            Assert::boolean($data['isregexpscope'], ArrayValidationException::class);
            $retval['isRegexpScope'] = $data['isregexpscope'];
        }

        return $retval;
    }


    /**
     * Create an array from this class
     *
     * @return array{
     *   'scope': string,
     *   'isRegexpScope'?: bool,
     * }
     */
    public function toArray(): array
    {
        $isRegexpScope = $this->isRegexpScope()?->toBoolean();

        return [
            'scope' => $this->getContent()->getValue(),
        ] + (isset($isRegexpScope) ? ['isRegexpScope' => $isRegexpScope] : []);
    }
}
