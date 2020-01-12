<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
abstract class AbstractSamlElement extends AbstractXMLElement
{
    /** @var string */
    protected $namespace = Constants::NS_SAML;

    /** @var string */
    protected $ns_prefix = 'saml';
}
