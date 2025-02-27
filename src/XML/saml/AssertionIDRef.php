<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\Type\NCNameValue;
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class representing a saml:AssertionIDRef element.
 *
 * @package simplesaml/saml2
 */
final class AssertionIDRef extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;

    /** @var string */
    public const TEXTCONTENT_TYPE = NCNameValue::class;
}
