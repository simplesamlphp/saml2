<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\shibmd;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractShibmdElement extends AbstractElement
{
    public const string NS = C::NS_SHIBMD;

    public const string NS_PREFIX = 'shibmd';

    public const string SCHEMA = 'resources/schemas/sstc-saml-metadata-shibmd-v1.0.xsd';
}
