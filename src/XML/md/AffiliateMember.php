<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class implementing AffiliateMember.
 *
 * @package simplesamlphp/saml2
 */
final class AffiliateMember extends AbstractMdElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = EntityIDValue::class;
}
