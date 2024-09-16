<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\alg;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractAlgElement extends AbstractElement
{
    /** @var string */
    public const NS = C::NS_ALG;

    /** @var string */
    public const NS_PREFIX = 'alg';
}
