<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\idpdisc;

use SimpleSAML\Assert\Assert;
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


    /**
     * DiscoveryResponse constructor.
     *
     * This is an endpoint with one restriction: it cannot contain a ResponseLocation.
     *
     * @param int $index
     * @param string $binding
     * @param string $location
     * @param bool|null $isDefault
     * @param string|null $unused
     * @param list<\SimpleSAML\XML\Attribute> $attributes
     * @param array $children
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        int $index,
        string $binding,
        string $location,
        ?bool $isDefault = null,
        ?string $unused = null,
        array $attributes = [],
        array $children = [],
    ) {
        Assert::null(
            $unused,
            'The \'ResponseLocation\' attribute must be omitted for idpdisc:DiscoveryResponse.',
        );
        parent::__construct($index, $binding, $location, $isDefault, null, $attributes, $children);
    }
}
