<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class representing a saml:AssertionURIRef element.
 *
 * @package simplesaml/saml2
 */
final class AssertionURIRef extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    /** @var string */
    public const TEXTCONTENT_TYPE = SAMLAnyURIValue::class;
}
