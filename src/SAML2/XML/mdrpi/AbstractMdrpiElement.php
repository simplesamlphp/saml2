<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/saml-metadata-rpi/v1.0/saml-metadata-rpi-v1.0.pdf
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractMdrpiElement extends AbstractElement
{
    /** @var string */
    public const NS = C::NS_MDRPI;

    /** @var string */
    public const NS_PREFIX = 'mdrpi';
}
