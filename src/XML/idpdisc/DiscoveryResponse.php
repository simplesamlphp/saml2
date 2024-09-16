<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\idpdisc;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
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
     * @param array<\SimpleSAML\XML\SerializableElementInterface> $children
     * @param array<\SimpleSAML\XML\Attribute> $attributes
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        int $index,
        string $binding,
        string $location,
        ?bool $isDefault = null,
        ?string $unused = null,
        array $children = [],
        array $attributes = [],
    ) {
        Assert::same($binding, C::BINDING_IDPDISC, ProtocolViolationException::class);
        Assert::null(
            $unused,
            'The \'ResponseLocation\' attribute must be omitted for idpdisc:DiscoveryResponse.',
        );
        parent::__construct($index, C::BINDING_IDPDISC, $location, $isDefault, null, $children, $attributes);
    }
}
