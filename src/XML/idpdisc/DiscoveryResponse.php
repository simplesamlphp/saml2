<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\idpdisc;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Type\BooleanValue;
use SimpleSAML\XMLSchema\Type\UnsignedShortValue;

/**
 * Abstract class to be implemented by all the classes in this namespace
 *
 * @package simplesamlphp/saml2
 *
 * @see http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-idp-discovery.html
 */
final class DiscoveryResponse extends AbstractIndexedEndpointType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /** @var string */
    public const NS = C::NS_IDPDISC;

    /** @var string */
    public const NS_PREFIX = 'idpdisc';

    /** @var string */
    public const SCHEMA = 'resources/schemas/sstc-saml-idp-discovery.xsd';


    /**
     * DiscoveryResponse constructor.
     *
     * This is an endpoint with one restriction: it cannot contain a ResponseLocation.
     *
     * @param \SimpleSAML\XMLSchema\Type\UnsignedShortValue $index
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $binding
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $location
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue|null $isDefault
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $responseLocation
     * @param array<\SimpleSAML\XML\SerializableElementInterface> $children
     * @param array<\SimpleSAML\XML\Attribute> $attributes
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        UnsignedShortValue $index,
        SAMLAnyURIValue $binding,
        SAMLAnyURIValue $location,
        ?BooleanValue $isDefault = null,
        ?SAMLAnyURIValue $responseLocation = null, // unused
        array $children = [],
        array $attributes = [],
    ) {
        Assert::same($binding->getValue(), C::BINDING_IDPDISC, ProtocolViolationException::class);
        Assert::null(
            $responseLocation,
            'The \'ResponseLocation\' attribute must be omitted for idpdisc:DiscoveryResponse.',
        );
        parent::__construct($index, $binding, $location, $isDefault, null, $children, $attributes);
    }
}
