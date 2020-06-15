<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SimpleSAML\Assert\Assert;

/**
 * Class representing an md:SingleSignOnService element.
 *
 * @package simplesamlphp/saml2
 */
final class SingleSignOnService extends AbstractEndpointType
{
    /**
     * SingleSignOnService constructor.
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
        array $attributes = []
    ) {
        Assert::null(
            $unused,
            'The \'ResponseLocation\' attribute must be omitted for md:SingleSignOnService.'
        );
        parent::__construct($binding, $location, null, $attributes);
    }
}
