<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractSamlpElement extends AbstractElement
{
    /** @var string */
    public const NS = C::NS_SAMLP;

    /** @var string */
    public const NS_PREFIX = 'samlp';

    /** @var string */
    public const SCHEMA = 'resources/schemas/saml-schema-protocol-2.0.xsd';
}
