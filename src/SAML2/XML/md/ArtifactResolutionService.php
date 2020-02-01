<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use Webmozart\Assert\Assert;

/**
 * A class implementing the md:ArtifactResolutionService element.
 *
 * @package simplesamlphp/saml2
 */
final class ArtifactResolutionService extends AbstractIndexedEndpointType
{
    /**
     * ArtifactResolutionService constructor.
     *
     * This is an endpoint with one restriction: it cannot contain a ResponseLocation.
     *
     * @param int $index
     * @param string $binding
     * @param string $location
     * @param bool|null $isDefault
     * @param string|null $unused
     * @param array|null $attributes
     */
    public function __construct(
        int $index,
        string $binding,
        string $location,
        ?bool $isDefault = null,
        ?string $unused = null,
        ?array $attributes = null
    ) {
        Assert::null(
            $unused,
            'The \'ResponseLocation\' attribute must be omitted for md:ArtifactResolutionService.'
        );
        parent::__construct($index, $binding, $location, $isDefault, null, $attributes);
    }
}
