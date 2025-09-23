<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class implementing AttributeProfile.
 *
 * @package simplesamlphp/saml2
 */
final class AttributeProfile extends AbstractMdElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    /** @var string */
    public const TEXTCONTENT_TYPE = SAMLAnyURIValue::class;
}
