<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class representing a samlp:StatusMessage element.
 *
 * @package simplesaml/saml2
 */
final class StatusMessage extends AbstractSamlpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = SAMLStringValue::class;
}
