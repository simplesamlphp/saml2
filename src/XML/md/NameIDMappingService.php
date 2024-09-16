<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\Assert\Assert;

/**
 * Class representing an md:NameIDMappingService element.
 *
 * @package simplesamlphp/saml2
 */
final class NameIDMappingService extends AbstractEndpointType
{
    /**
     * NameIDMappingService constructor.
     *
     * This is an endpoint with one restriction: it cannot contain a ResponseLocation.
     *
     * @param string $binding
     * @param string $location
     * @param string|null $unused
     * @param array $attributes
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        string $binding,
        string $location,
        ?string $unused = null,
        array $attributes = [],
    ) {
        Assert::null(
            $unused,
            'The \'ResponseLocation\' attribute must be omitted for md:NameIDMappingService.',
        );
        parent::__construct($binding, $location, null, $attributes);
    }
}
