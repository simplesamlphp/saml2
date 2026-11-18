<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\AuthnContextComparisonTypeValue;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;

use function array_merge;
use function strval;

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
     * @param \SimpleSAML\SAML2\Type\AuthnContextComparisonTypeValue $Comparison
     */
    public function __construct(
        protected array $requestedAuthnContexts = [],
        protected ?AuthnContextComparisonTypeValue $Comparison = null,
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
     * @return \SimpleSAML\SAML2\Type\AuthnContextComparisonTypeValue|null
     */
    public function getComparison(): ?AuthnContextComparisonTypeValue
    {
        return $this->Comparison;
    }


    /**
     * Convert XML into a RequestedAuthnContext
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'RequestedAuthnContext', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RequestedAuthnContext::NS, InvalidDOMElementException::class);

        return new static(
            array_merge(
                AuthnContextClassRef::getChildrenOfClass($xml),
                AuthnContextDeclRef::getChildrenOfClass($xml),
            ),
            self::getOptionalAttribute($xml, 'Comparison', AuthnContextComparisonTypeValue::class, null),
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
            $e->setAttribute('Comparison', strval($this->getComparison()));
        }

        return $e;
    }
}
