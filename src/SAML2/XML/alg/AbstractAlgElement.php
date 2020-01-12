<?php

declare(strict_types=1);

namespace SAML2\XML\alg;

use SAML2\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
abstract class AbstractAlgElement extends AbstractXMLElement
{
    /** @var string */
    protected $namespace = 'urn:oasis:names:tc:SAML:metadata:algsupport';

    /** @var string */
    protected $ns_prefix = 'alg';
}
