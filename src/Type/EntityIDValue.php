<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Type;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;

/**
 * @package simplesaml/saml2
 */
class EntityIDValue extends SAMLAnyURIValue
{
    /** @var string */
    public const SCHEMA_TYPE = 'entityIDType';

    /** @var string */
    public const SCHEMA_NAMESPACEURI = C::NS_MD;

    /** @var string */
    public const SCHEMA_NAMESPACE_PREFIX = AbstractMdElement::NS_PREFIX;

    /**
     * Validate the value.
     *
     * @param string $value
     * @return void
     */
    protected function validateValue(string $value): void
    {
        // Note: value must already be sanitized before validating
        Assert::validEntityID($this->sanitizeValue($value));
    }
}
