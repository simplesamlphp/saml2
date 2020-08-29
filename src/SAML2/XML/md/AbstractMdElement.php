<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\AbstractXMLElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
abstract class AbstractMdElement extends AbstractXMLElement
{
    /** @var string */
    public const NS = Constants::NS_MD;

    /** @var string */
    public const NS_PREFIX = 'md';
}
