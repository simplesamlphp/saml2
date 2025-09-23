<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use SimpleSAML\SAML2\Type\CIDRValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class implementing IPHint.
 *
 * @package simplesamlphp/saml2
 */
final class IPHint extends AbstractMduiElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;


    /** @var string */
    public const TEXTCONTENT_TYPE = CIDRValue::class;
}
