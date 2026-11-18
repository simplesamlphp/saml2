<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractSamlElement extends AbstractElement
{
    public const string NS = C::NS_SAML;

    public const string NS_PREFIX = 'saml';

    public const string SCHEMA = 'resources/schemas/saml-schema-assertion-2.0.xsd';
}
