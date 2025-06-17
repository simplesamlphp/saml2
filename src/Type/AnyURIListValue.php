<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\Type\ListTypeInterface;

use function array_map;
use function preg_split;
use function str_replace;
use function trim;

/**
 * @package simplesaml/saml2
 */
class AnyURIListValue extends SAMLAnyURIValue implements ListTypeInterface
{
    /** @var string */
    public const SCHEMA_TYPE = 'AnyURIList';


    /**
     * Validate the value.
     *
     * @param string $value
     * @throws \SimpleSAML\XML\Exception\SchemaViolationException on failure
     * @return void
     */
    protected function validateValue(string $value): void
    {
        $uris = preg_split('/[\s]+/', $this->sanitizeValue($value), C::UNBOUNDED_LIMIT);

        Assert::allValidAnyURI($uris, SchemaViolationException::class);
    }


    /**
     * Convert an array of xs:anyURI items into a saml:AnyURIList
     *
     * @param string[] $uris
     * @return static
     */
    public static function fromArray(array $uris): static
    {
        $str = '';
        foreach ($uris as $uri) {
            $str .= str_replace(' ', '+', $uri) . ' ';
        }

        return new static(trim($str));
    }


    /**
     * Convert this saml:AnyURIList to an array of xs:anyURI items
     *
     * @return array<\SimpleSAML\SAML2\Type\SAMLAnyURIValue>
     */
    public function toArray(): array
    {
        $uris = preg_split('/[\s]+/', $this->getValue(), C::UNBOUNDED_LIMIT);
        $uris = str_replace('+', ' ', $uris);

        return array_map([SAMLAnyURIValue::class, 'fromString'], $uris);
    }
}
