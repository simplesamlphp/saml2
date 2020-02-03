<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
abstract class AbstractDsElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = XMLSecurityDSig::XMLDSIGNS;

    /** @var string */
    public const NS_PREFIX = 'ds';
}
