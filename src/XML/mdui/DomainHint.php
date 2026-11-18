<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Type\DomainValue;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\TypedTextContentTrait;

use function array_change_key_case;
use function array_keys;

/**
 * Class implementing DomainHint.
 *
 * @package simplesamlphp/saml2
 */
final class DomainHint extends AbstractMduiElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = DomainValue::class;


    /**
     * Create a class from an array
     *
     * @param array{
     *   'url': string,
     * } $data
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            DomainValue::fromString($data['hint']),
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array{
     *   'hint': string,
     * } $data
     * @return array{
     *   'hint': string,
     * }
     */
    private static function processArrayContents(array $data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);

        // Make sure the array keys are known for this kind of object
        Assert::allOneOf(
            array_keys($data),
            [
                'hint',
            ],
            ArrayValidationException::class,
        );

        Assert::keyExists($data, 'hint', ArrayValidationException::class);
        Assert::string($data['hint'], ArrayValidationException::class);

        return [
            'hint' => $data['hint'],
        ];
    }


    /**
     * Create an array from this class
     *
     * @return array{
     *   'hint': string,
     * }
     */
    public function toArray(): array
    {
        return [
            'hint' => $this->getContent()->getValue(),
        ];
    }
}
