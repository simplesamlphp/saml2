<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use SimpleSAML\SAML2\Type\DomainValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class implementing DomainHint.
 *
 * @package simplesamlphp/saml2
 */
final class DomainHint extends AbstractMduiElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = DomainValue::class;
}
