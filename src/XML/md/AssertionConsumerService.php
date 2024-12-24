<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * Class implementing the md:AssertionConsumerService element.
 *
 * @package simplesamlphp/saml2
 */
final class AssertionConsumerService extends AbstractIndexedEndpointType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
}
