<?php

declare(strict_types=1);

namespace SAML2\XML\xenc;

use SAML2\Constants;
use SAML2\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractXencElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = Constants::NS_XENC;

    /** @var string */
    public const NS_PREFIX = 'xenc';
}
