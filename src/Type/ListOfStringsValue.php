<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSchema\Type\Interface\ListTypeInterface;

use function array_map;
use function preg_split;
use function str_replace;
use function trim;

/**
 * @package simplesaml/saml2
 */
class ListOfStringsValue extends SAMLStringValue implements ListTypeInterface
{
    public const string SCHEMA_TYPE = 'ListOfStrings';

    public const string SCHEMA_NAMESPACEURI = C::NS_MDUI;

    public const string SCHEMA_NAMESPACE_PREFIX = 'mdui';


    /**
     * Validate the value.
     *
     * @throws \SimpleSAML\XMLSchema\Exception\SchemaViolationException on failure
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
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue[]
     */
    public function toArray(): array
    {
        $strings = preg_split('/[\s]+/', $this->getValue(), C::UNBOUNDED_LIMIT);
        $strings = str_replace('+', ' ', $strings);

        return array_map([SAMLStringValue::class, 'fromString'], $strings);
    }
}
