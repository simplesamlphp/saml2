<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\TypedTextContentTrait;

use function array_key_first;
use function strval;

/**
 * Class representing a samlp:GetComplete element.
 *
 * @package simplesaml/saml2
 */
final class GetComplete extends AbstractSamlpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;

    /** @var string */
    public const TEXTCONTENT_TYPE = SAMLAnyURIValue::class;


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
        return new static(
            SAMLAnyURIValue::fromString($data[$index]),
        );
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        return [strval($this->getContent())];
    }
}
