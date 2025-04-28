<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\Type\ListTypeInterface;

use function array_map;
use function preg_split;
use function str_replace;
use function trim;

/**
 * @package simplesaml/saml2
 */
class ListOfStringsValue extends SAMLStringValue implements ListTypeInterface
{
    /** @var string */
    public const SCHEMA_TYPE = 'ListOfStrings';

    /** @var string */
    public const SCHEMA_NAMESPACEURI = C::NS_MDUI;

    /** @var string */
    public const SCHEMA_NAMESPACE_PREFIX = 'mdui';


    /**
     * Validate the value.
     *
     * @param string $value
     * @throws \SimpleSAML\XML\Exception\SchemaViolationException on failure
     * @return void
     */
    protected function validateValue(string $value): void
    {
        $strings = preg_split('/[\s]+/', $this->sanitizeValue($value), C::UNBOUNDED_LIMIT);

        Assert::allValidString($strings, SchemaViolationException::class);
    }


    /**
     * Convert an array of xs:string items into a mdui:ListOfStrings
     *
     * @param string[] $keywords
     * @return static
     */
    public static function fromArray(array $keywords): static
    {
        Assert::allNotContains($keywords, '+', ProtocolViolationException::class);

        $str = '';
        foreach ($keywords as $keyword) {
            $str .= str_replace(' ', '+', $keyword) . ' ';
        }

        return new static(trim($str));
    }


    /**
     * Convert this mdui:ListOfStrings to an array of xs:string items
     *
     * @return array<\SimpleSAML\SAML2\Type\SAMLStringValue>
     */
    public function toArray(): array
    {
        $strings = preg_split('/[\s]+/', $this->getValue(), C::UNBOUNDED_LIMIT);
        $strings = str_replace('+', ' ', $strings);

        return array_map([SAMLStringValue::class, 'fromString'], $strings);
    }
}
