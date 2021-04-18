<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractSamlpElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = Constants::NS_SAMLP;

    /** @var string */
    public const NS_PREFIX = 'samlp';
}
