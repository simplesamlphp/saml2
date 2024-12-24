<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\emd;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractEmdElement extends AbstractElement
{
    /** @var string */
    public const NS = C::NS_EMD;

    /** @var string */
    public const NS_PREFIX = 'emd';

    /** @var string */
    public const SCHEMA = 'resources/schemas/eduidmd.xsd';
}
