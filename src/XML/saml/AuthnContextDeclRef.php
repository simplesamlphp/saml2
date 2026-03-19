<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class representing SAML2 AuthnContextDeclRef
 *
 * @package simplesamlphp/saml2
 */
final class AuthnContextDeclRef extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = SAMLAnyURIValue::class;
}
