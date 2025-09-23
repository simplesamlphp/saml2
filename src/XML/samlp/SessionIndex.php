<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class representing a samlp:SessionIndex element.
 *
 * @package simplesaml/saml2
 */
final class SessionIndex extends AbstractSamlpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    /** @var string */
    public const TEXTCONTENT_TYPE = SAMLStringValue::class;
}
