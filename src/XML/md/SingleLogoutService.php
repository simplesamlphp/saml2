<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\aslo\SupportsAsynchronousTrait;

/**
 * SingleLogoutService element of type EndpointType
 *
 * @package simplesamlphp/saml2
 */
final class SingleLogoutService extends AbstractEndpointType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use SupportsAsynchronousTrait;


    /**
     * SingleLogoutService constructor.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $binding
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $location
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $responseLocation
     * @param \SimpleSAML\XML\ElementInterface[] $children
     * @param array<\SimpleSAML\XML\Attribute> $attributes
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        protected SAMLAnyURIValue $binding,
        protected SAMLAnyURIValue $location,
        protected ?SAMLAnyURIValue $responseLocation = null,
        array $children = [],
        array $attributes = [],
    ) {
        $this->setSupportsAsynchronous($attributes);

        parent::__construct($binding, $location, $responseLocation, $children, $attributes);
    }
}
