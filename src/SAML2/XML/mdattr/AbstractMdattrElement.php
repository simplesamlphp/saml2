<?php

declare(strict_types=1);

namespace SAML2\XML\mdattr;

use SAML2\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
abstract class AbstractMdattrElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = 'urn:oasis:names:tc:SAML:metadata:attribute';

    /** @var string */
    public const NS_PREFIX = 'mdattr';
}
