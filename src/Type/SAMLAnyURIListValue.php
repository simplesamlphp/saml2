<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSchema\Type\Helper\AnyURIListValue;

use function array_map;
use function preg_split;

/**
 * @package simplesaml/saml2
 */
class SAMLAnyURIListValue extends AnyURIListValue
{
    /** @var string */
    public const SCHEMA_TYPE = 'AnyURIList';


    /**
     * Validate the value.
     *
     * @param string $value
     * @throws \SimpleSAML\XMLSchema\Exception\SchemaViolationException on failure
     * @return void
     */
    protected function validateValue(string $value): void
    {
        $sanitized = $this->sanitizeValue($value);
        Assert::stringNotEmpty($sanitized, ProtocolViolationException::class);

        $uris = preg_split('/[\s]+/', $sanitized, C::UNBOUNDED_LIMIT);
        Assert::allValidAnyURI($uris, SchemaViolationException::class);
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
