<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Utils;
use SAML2\XML\saml\AuthnContextClassRef;
use SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SAML2 RequestedAuthnContext
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class RequestedAuthnContext extends AbstractSamlpElement
{
    /** @var (\SAML2\XML\saml\AuthnContextClassRef|\SAML2\XML\saml\AuthnContextDeclRef)[] */
    protected $requestedAuthnContexts = [];

    /** @var string|null */
    protected $Comparison = null;


    /**
     * Initialize a RequestedAuthnContext.
     *
     * @param (\SAML2\XML\saml\AuthnContextClassRef|\SAML2\XML\saml\AuthnContextDeclRef)[] $requestedAuthnContexts
     * @param string $Comparison
     */
    public function __construct(
        array $requestedAuthnContexts = [],
        string $Comparison = null
    ) {
        $this->setRequestedAuthnContexts($requestedAuthnContexts);
        $this->setComparison($Comparison);
    }


    /**
     * Collect the value of the requestedAuthnContexts-property
     *
     * @return (\SAML2\XML\saml\AuthnContextClassRef|\SAML2\XML\saml\AuthnContextDeclRef)[]
     */
    public function getRequestedAuthnContexts(): array
    {
        return $this->requestedAuthnContexts;
    }


    /**
     * Set the value of the requestedAuthnContexts-property
     *
     * @param (\SAML2\XML\saml\AuthnContextClassRef|\SAML2\XML\saml\AuthnContextDeclRef)[] $requestedAuthnContexts
     * @return void
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SAML2\Exception\InvalidDOMElementException if the supplied element is missing the Algorithm attribute
     */
    private function setRequestedAuthnContexts(array $requestedAuthnContexts): void
    {
        Assert::minCount($requestedAuthnContexts, 1);
        Assert::allIsInstanceOfAny($requestedAuthnContexts, [AuthnContextClassRef::class, AuthnContextDeclRef::class]);

        if ($requestedAuthnContexts[0] instanceof AuthnContextClassRef) {
            Assert::allIsInstanceOf(
                $requestedAuthnContexts,
                AuthnContextClassRef::class,
                'You need either AuthnContextClassRef or AuthnContextDeclRef, not both.'
            );
        } elseif ($requestedAuthnContexts[0] instanceof AuthnContextDeclRef) {
            Assert::allIsInstanceOf(
                $requestedAuthnContexts,
                AuthnContextDeclRef::class,
                'You need either AuthnContextClassRef or AuthnContextDeclRef, not both.'
            );
        }

        $this->requestedAuthnContexts = $requestedAuthnContexts;
    }


    /**
     * Collect the value of the Comparison-property
     *
     * @return string|null
     */
    public function getComparison(): ?string
    {
        return $this->Comparison;
    }


    /**
     * Set the value of the Comparison-property
     *
     * @param string|null $comparison
     * @return void
     */
    private function setComparison(?string $comparison): void
    {
        $this->Comparison = $comparison;
    }


    /**
     * Convert XML into a RequestedAuthnContext
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\samlp\RequestedAuthnContext
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'RequestedAuthnContext', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RequestedAuthnContext::NS, InvalidDOMElementException::class);

        return new self(
            array_merge(
                AuthnContextClassRef::getChildrenOfClass($xml),
                AuthnContextDeclRef::getChildrenOfClass($xml)
            ),
            self::getAttribute($xml, 'Comparison', null)
        );
    }


    /**
     * Convert this RequestedAuthnContext to XML.
     *
     * @param \DOMElement|null $parent The element we should append this RequestedAuthnContext to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = $this->instantiateParentElement($parent);

        foreach ($this->requestedAuthnContexts as $context) {
            $e->appendChild($e->ownerDocument->importNode($context->toXML(), true));
        }

        if (isset($this->Comparison)) {
            Assert::oneOf($this->Comparison, ['exact', 'minimum', 'maximum', 'better']);
            $e->setAttribute('Comparison', $this->Comparison);
        }

        return $e;
    }
}
