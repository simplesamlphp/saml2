<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\samlp\AuthnContextComparisonTypeEnum;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSchema\Type\StringValue;

use function array_column;

/**
 * @package simplesamlphp/saml2
 */
class AuthnContextComparisonTypeValue extends StringValue
{
    public const string SCHEMA_TYPE = 'authnContextComparisonType';


    /**
     * Validate the value.
     *
     * @throws \Exception on failure
     */
    protected function validateValue(string $value): void
    {
        Assert::oneOf(
            $this->sanitizeValue($value),
            array_column(AuthnContextComparisonTypeEnum::cases(), 'value'),
            SchemaViolationException::class,
        );
    }


    /**
     * @param \SimpleSAML\SAML2\XML\samlp\AuthnContextComparisonTypeEnum $value
     */
    public static function fromEnum(AuthnContextComparisonTypeEnum $value): static
    {
        return new static($value->value);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\AuthnContextComparisonTypeEnum $value
     */
    public function toEnum(): AuthnContextComparisonTypeEnum
    {
        return AuthnContextComparisonTypeEnum::from($this->getValue());
    }
}
