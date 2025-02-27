<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class representing SAML2 AuthnContextClassRef
 *
 * @package simplesamlphp/saml2
 */
final class AuthnContextClassRef extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;

    /** @var string */
    public const TEXTCONTENT_TYPE = SAMLAnyURIValue::class;
}
