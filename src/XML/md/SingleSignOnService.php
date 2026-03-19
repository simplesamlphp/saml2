<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * Class representing an md:SingleSignOnService element.
 *
 * @package simplesamlphp/saml2
 */
final class SingleSignOnService extends AbstractEndpointType implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * SingleSignOnService constructor.
     *
     * This is an endpoint with one restriction: it cannot contain a ResponseLocation.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $binding
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $location
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $unused
     * @param array $attributes
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        SAMLAnyURIValue $binding,
        SAMLAnyURIValue $location,
        ?SAMLAnyURIValue $unused = null,
        array $attributes = [],
    ) {
        Assert::null(
            $unused,
            'The \'ResponseLocation\' attribute must be omitted for md:SingleSignOnService.',
            ProtocolViolationException::class,
        );
        parent::__construct($binding, $location, null, $attributes);
    }
}
