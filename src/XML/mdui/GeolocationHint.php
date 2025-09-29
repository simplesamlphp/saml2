<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use SimpleSAML\SAML2\Type\GeolocationValue;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class implementing GeolocationHint.
 *
 * @package simplesamlphp/saml2
 */
final class GeolocationHint extends AbstractMduiElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;

    /** @var string */
    public const TEXTCONTENT_TYPE = GeolocationValue::class;
}
