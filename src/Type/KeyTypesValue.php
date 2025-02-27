<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\XML\md\KeyTypesEnum;
use SimpleSAML\XML\Exception\SchemaViolationException;

use function array_column;

/**
 * @package simplesaml/saml2
 */
class KeyTypesValue extends SAMLStringValue
{
    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateValue(string $value): void
    {
        Assert::oneOf(
            $this->sanitizeValue($value),
            array_column(KeyTypesEnum::cases(), 'value'),
            SchemaViolationException::class,
        );
    }


    /**
     * Convert enum to value type
     */
    public static function fromEnum(KeyTypesEnum $use): static
    {
        return static::fromString($use->value);
    }


    /**
     * Convert this value type to enum
     */
    public function toEnum(): KeyTypesEnum
    {
        return KeyTypesEnum::from($this->getValue());
    }
}
