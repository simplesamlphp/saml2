<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\AbstractElement;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 *
 * @see http://docs.oasis-open.org/security/saml/Post2.0/saml-ecp/v2.0/saml-ecp-v2.0.html
 */
abstract class AbstractEcpElement extends AbstractElement
{
    /** @var string */
    public const NS = C::NS_ECP;

    /** @var string */
    public const NS_PREFIX = 'ecp';
}
