<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\idpdisc;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 *
 * @see http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-idp-discovery.html
 */
final class DiscoveryResponse extends AbstractIndexedEndpointType
{
    /** @var string */
    public const NS = C::NS_IDPDISC;

    /** @var string */
    public const NS_PREFIX = 'idpdisc';
}
