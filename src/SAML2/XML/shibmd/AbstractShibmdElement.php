<?php

declare(strict_types=1);

namespace SAML2\XML\shibmd;

use SAML2\Constants;
use SAML2\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
abstract class AbstractShibmdElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = 'urn:mace:shibboleth:metadata:1.0';

    /** @var string */
    public const NS_PREFIX = 'shibmd';
}
