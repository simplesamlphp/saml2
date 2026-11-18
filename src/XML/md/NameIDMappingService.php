<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * Class representing an md:NameIDMappingService element.
 *
 * @package simplesamlphp/saml2
 */
final class NameIDMappingService extends AbstractEndpointType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * NameIDMappingService constructor.
     *
     * This is an endpoint with one restriction: it cannot contain a ResponseLocation.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $binding
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $location
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $responseLocation
     * @param \SimpleSAML\XML\Attribute[] $attributes
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        SAMLAnyURIValue $binding,
        SAMLAnyURIValue $location,
        ?SAMLAnyURIValue $responseLocation = null,
        array $attributes = [],
    ) {
        Assert::null(
            $responseLocation,
            'The \'ResponseLocation\' attribute must be omitted for md:NameIDMappingService.',
        );

        parent::__construct($binding, $location, null, $attributes);
    }
}
