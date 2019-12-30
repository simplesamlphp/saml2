<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\saml\AuthnContextClassRef;
use SAML2\XML\saml\AuthnContextDeclRef;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML2 RequestedAuthnContext
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
class RequestedAuthnContext
{
    /** @var (\SAML2\XML\saml\AuthnContextClassRef|\SAML2\XML\saml\AuthnContextDeclRef)[] */
    private $requestedAuthnContexts = [];

    /** @var string|null */
    private $Comparison = null;


    /**
     * Initialize a RequestedAuthnContext.
     *
     * @param (\SAML2\XML\saml\AuthnContextClassRef|\SAML2\XML\saml\AuthnContextDeclRef)[] $requestedAuthnContexts
     * @param string $Comparison
     */
    public function __construct(array $requestedAuthnContexts, string $Comparison = null)
    {
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
     * @param (\SAML2\XML\saml\AuthnContextClassRef|\SAML2\XML\saml\AuthnContextDeclRef|mixed)[] $requestedAuthnContexts
     * @return void
     */
    public function setRequestedAuthnContexts(array $requestedAuthnContexts): void
    {
        Assert::minCount($requestedAuthnContexts, 1);
        Assert::allIsInstanceOfAny($requestedAuthnContexts, [AuthnContextClassRef::class, AuthnContextDeclRef::class]);

        if ($requestedAuthnContexts[0] instanceof AuthnContextClassRef) {
            Assert::allIsInstanceOf($requestedAuthnContexts, AuthnContextClassRef::class, 'You need either AuthnContextClassRef or AuthnContextDeclRef, not both.');
        } elseif ($requestedAuthnContexts[0] instanceof AuthnContextDeclRef) {
            Assert::allIsInstanceOf($requestedAuthnContexts, AuthnContextDeclRef::class, 'You need either AuthnContextClassRef or AuthnContextDeclRef, not both.');
        } else {
            throw new \InvalidArgumentException('You need either AuthnContextClassRef or AuthnContextDeclRef.');
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
    public function setComparison(?string $comparison): void
    {
        $this->Comparison = $comparison;
    }


    /**
     * Convert XML into a RequestedAuthnContext
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        /** @var \DOMElement[] $authnContextClassRef */
        $authnContextClassRef = Utils::xpQuery($xml, './saml_assertion:AuthnContextClassRef');

        /** @var \DOMElement[] $authnContextDeclRef */
        $authnContextDeclRef = Utils::xpQuery($xml, './saml_assertion:AuthnContextDeclRef');

        Assert::oneOf(
            [],
            [$authnContextClassRef, $authnContextDeclRef],
            'You need either AuthnContextClassRef or AuthnContextDeclRef, not both.'
        );

        $requestedAuthnContexts = [];

        foreach ($authnContextClassRef as $classRef) {
            $requestedAuthnContexts[] = AuthnContextClassRef::fromXML($classRef);
        }

        foreach ($authnContextDeclRef as $declRef) {
            $requestedAuthnContexts[] = AuthnContextDeclRef::fromXML($declRef);
        }

        $Comparison = null;
        if ($xml->hasAttribute('Comparison')) {
            $Comparison = $xml->getAttribute('Comparison');
        }

        return new self($requestedAuthnContexts, $Comparison);
    }


    /**
     * Convert this RequestedAuthnContext to XML.
     *
     * @param \DOMElement|null $parent The element we should append this RequestedAuthnContext to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS(Constants::NS_SAMLP, 'samlp:RequestedAuthnContext');
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS(Constants::NS_SAMLP, 'samlp:RequestedAuthnContext');
            $parent->appendChild($e);
        }

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
