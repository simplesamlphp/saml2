<?php

declare(strict_types=1);

namespace SAML2\XML\init;

use DOMElement;
use SAML2\XML\md\AbstractEndpointType;
use Webmozart\Assert\Assert;

/**
 * Class for handling the init:RequestInitiator element.
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-request-initiation-cs-01.pdf
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class RequestInitiator extends AbstractEndpointType
{
    /** @var string */
    public const NS = 'urn:oasis:names:tc:SAML:profiles:SSO:request-init';

    /** @var string */
    public const NS_PREFIX = 'init';


    /**
     * Set the value of the Binding property.
     *
     * @param string $binding
     * @throws \InvalidArgumentException if the Binding is empty
     */
    protected function setBinding(string $binding): void
    {
        Assert::notEmpty($binding, 'The Binding of an endpoint cannot be empty.');
        Assert::eq($binding, self::NS, "The Binding of a RequestInitiator must be 'urn:oasis:names:tc:SAML:profiles:SSO:request-init'.");

        $this->Binding = $binding;
    }
}
