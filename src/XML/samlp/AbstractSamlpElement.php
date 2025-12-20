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
    public const string NS = C::NS_SAMLP;

    public const string NS_PREFIX = 'samlp';

    public const string SCHEMA = 'resources/schemas/saml-schema-protocol-2.0.xsd';
}
