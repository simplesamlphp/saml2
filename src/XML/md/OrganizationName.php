<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * A localized name representing an organization's name.
 *
 * @package simplesamlphp/saml2
 */
final class OrganizationName extends AbstractLocalizedName implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
}
