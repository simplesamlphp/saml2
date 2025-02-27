<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\Type\{BooleanValue, UnsignedShortValue};

/**
 * A class implementing the md:ArtifactResolutionService element.
 *
 * @package simplesamlphp/saml2
 */
final class ArtifactResolutionService extends AbstractIndexedEndpointType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * ArtifactResolutionService constructor.
     *
     * This is an endpoint with one restriction: it cannot contain a ResponseLocation.
     *
     * @param \SimpleSAML\XML\Type\UnsignedShortValue $index
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $binding
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $location
     * @param \SimpleSAML\XML\Type\BooleanValue|null $isDefault
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $unused
     * @param list<\SimpleSAML\XML\Attribute> $attributes
     * @param array $children
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        UnsignedShortValue $index,
        SAMLAnyURIValue $binding,
        SAMLAnyURIValue $location,
        ?BooleanValue $isDefault = null,
        ?SAMLAnyURIValue $unused = null,
        array $children = [],
        array $attributes = [],
    ) {
        Assert::null(
            $unused,
            'The \'ResponseLocation\' attribute must be omitted for md:ArtifactResolutionService.',
        );
        parent::__construct($index, $binding, $location, $isDefault, null, $children, $attributes);
    }
}
