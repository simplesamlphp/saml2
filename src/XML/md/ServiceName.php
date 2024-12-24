<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * A localized name representing a service's name.
 *
 * @package simplesamlphp/saml2
 */
final class ServiceName extends AbstractLocalizedName implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
}
