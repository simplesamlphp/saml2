<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};

/**
 * A localized name representing an organization's url.
 *
 * @package simplesamlphp/saml2
 */
final class OrganizationURL extends AbstractLocalizedURI implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
}
