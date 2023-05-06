<?php

declare(strict_types=1);

namespace SAML2\XML\ecp;

use SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractEcpElement extends AbstractElement
{
    /** @var string */
    public const NS = C::NS_ECP;

    /** @var string */
    public const NS_PREFIX = 'ecp';
}
