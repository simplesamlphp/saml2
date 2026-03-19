<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * AttributeService elements of type EndpointType
 *
 * @package simplesamlphp/saml2
 */
final class AttributeService extends AbstractEndpointType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
}
