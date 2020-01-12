<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
abstract class AbstractDsElement extends AbstractXMLElement
{
    /** @var string */
    protected $namespace = XMLSecurityDSig::XMLDSIGNS;

    /** @var string */
    protected $ns_prefix = 'ds';
}
