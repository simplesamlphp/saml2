<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\Comparison;
use SimpleSAML\SAML2\XML\saml\{AuthnContextClassRef, AuthnContextDeclRef};
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\{InvalidDOMElementException, SchemaViolationException};
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};

use function array_merge;

/**
 * Class representing SAML2 RequestedAuthnContext
 *
 * @package simplesamlphp/saml2
 */
final class RequestedAuthnContext extends AbstractSamlpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize a RequestedAuthnContext.
     *
     * @param (
     *    \SimpleSAML\SAML2\XML\saml\AuthnContextClassRef|
     *    \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef
     * )[] $requestedAuthnContexts
     * @param \SimpleSAML\SAML2\XML\Comparison $Comparison
     */
    public function __construct(
        protected array $requestedAuthnContexts = [],
        protected ?Comparison $Comparison = null,
    ) {
        Assert::maxCount($requestedAuthnContexts, C::UNBOUNDED_LIMIT);
        Assert::minCount($requestedAuthnContexts, 1, SchemaViolationException::class);
        Assert::allIsInstanceOfAny(
            $requestedAuthnContexts,
            [AuthnContextClassRef::class, AuthnContextDeclRef::class],
            SchemaViolationException::class,
        );

        if ($requestedAuthnContexts[0] instanceof AuthnContextClassRef) {
            Assert::allIsInstanceOf(
                $requestedAuthnContexts,
                AuthnContextClassRef::class,
                'You need either AuthnContextClassRef or AuthnContextDeclRef, not both.',
                ProtocolViolationException::class,
            );
        } else { // Can only be AuthnContextDeclRef
            Assert::allIsInstanceOf(
                $requestedAuthnContexts,
                AuthnContextDeclRef::class,
                'You need either AuthnContextClassRef or AuthnContextDeclRef, not both.',
                ProtocolViolationException::class,
            );
        }
    }


    /**
     * Collect the value of the requestedAuthnContexts-property
     *
     * @return (\SimpleSAML\SAML2\XML\saml\AuthnContextClassRef|\SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef)[]
     */
    public function getRequestedAuthnContexts(): array
    {
        return $this->requestedAuthnContexts;
    }


    /**
     * Collect the value of the Comparison-property
     *
     * @return \SimpleSAML\SAML2\XML\Comparison|null
     */
    public function getComparison(): ?Comparison
    {
        return $this->Comparison;
    }


    /**
     * Convert XML into a RequestedAuthnContext
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'RequestedAuthnContext', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RequestedAuthnContext::NS, InvalidDOMElementException::class);

        $Comparison = self::getOptionalAttribute(
            $xml,
            'Comparison',
            SAMLStringValue::class,
            SAMLStringValue::fromString('unknown'),
        );

        return new static(
            array_merge(
                AuthnContextClassRef::getChildrenOfClass($xml),
                AuthnContextDeclRef::getChildrenOfClass($xml),
            ),
            Comparison::tryFrom($Comparison->getValue()),
        );
    }


    /**
     * Convert this RequestedAuthnContext to XML.
     *
     * @param \DOMElement|null $parent The element we should append this RequestedAuthnContext to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getRequestedAuthnContexts() as $context) {
            $context->toXML($e);
        }

        if ($this->getComparison() !== null) {
            $e->setAttribute('Comparison', $this->getComparison()->value);
        }

        return $e;
    }
}
