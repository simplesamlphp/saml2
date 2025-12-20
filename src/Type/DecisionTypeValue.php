<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\saml\DecisionTypeEnum;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSchema\Type\StringValue;

use function array_column;

/**
 * @package simplesamlphp/saml2
 */
class DecisionTypeValue extends StringValue
{
    public const string SCHEMA_TYPE = 'decisionType';


    /**
     * Validate the value.
     *
     * @throws \Exception on failure
     */
    protected function validateValue(string $value): void
    {
        Assert::oneOf(
            $this->sanitizeValue($value),
            array_column(DecisionTypeEnum::cases(), 'value'),
            SchemaViolationException::class,
        );
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\DecisionTypeEnum $value
     */
    public static function fromEnum(DecisionTypeEnum $value): static
    {
        return new static($value->value);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\DecisionTypeEnum $value
     */
    public function toEnum(): DecisionTypeEnum
    {
        return DecisionTypeEnum::from($this->getValue());
    }
}
