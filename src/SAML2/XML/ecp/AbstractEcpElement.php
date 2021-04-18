<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractEcpElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = Constants::NS_ECP;

    /** @var string */
    public const NS_PREFIX = 'ecp';
}
