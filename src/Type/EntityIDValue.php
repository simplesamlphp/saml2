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
    public const string SCHEMA_TYPE = 'entityIDType';

    public const string SCHEMA_NAMESPACEURI = C::NS_MD;

    public const string SCHEMA_NAMESPACE_PREFIX = AbstractMdElement::NS_PREFIX;


    /**
     * Validate the value.
     */
    protected function validateValue(string $value): void
    {
        // Note: value must already be sanitized before validating
        Assert::validEntityID($this->sanitizeValue($value));
    }
}
