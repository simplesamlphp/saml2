<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SOAP11\Constants as C;
use SimpleSAML\SOAP11\Type\MustUnderstandValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;

/**
 * Class representing the ECP Response element.
 *
 * @package simplesamlphp/saml2
 */
final class Response extends AbstractEcpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Create a ECP Response element.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $assertionConsumerServiceURL
     */
    public function __construct(
        protected SAMLAnyURIValue $assertionConsumerServiceURL,
    ) {
    }


    /**
     * Collect the value of the AssertionConsumerServiceURL-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue
     */
    public function getAssertionConsumerServiceURL(): SAMLAnyURIValue
    {
        return $this->assertionConsumerServiceURL;
    }


    /**
     * Convert XML into a Response
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Response', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Response::NS, InvalidDOMElementException::class);

        // Assert required attributes
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV, 'mustUnderstand'),
            'Missing env:mustUnderstand attribute in <ecp:Response>.',
            MissingAttributeException::class,
        );
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV, 'actor'),
            'Missing env:actor attribute in <ecp:Response>.',
            MissingAttributeException::class,
        );
        Assert::true(
            $xml->hasAttribute('AssertionConsumerServiceURL'),
            'Missing AssertionConsumerServiceURL attribute in <ecp:Response>.',
            MissingAttributeException::class,
        );

        MustUnderstandValue::fromString($xml->getAttributeNS(C::NS_SOAP_ENV, 'mustUnderstand'));

        Assert::same(
            $xml->getAttributeNS(C::NS_SOAP_ENV, 'actor'),
            C::SOAP_ACTOR_NEXT,
            'Invalid value of env:actor attribute in <ecp:Response>.',
            ProtocolViolationException::class,
        );

        return new static(self::getAttribute($xml, 'AssertionConsumerServiceURL', SAMLAnyURIValue::class));
    }


    /**
     * Convert this ECP Response to XML.
     *
     * @param \DOMElement|null $parent The element we should append this element to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $response = $this->instantiateParentElement($parent);

        $response->setAttributeNS(C::NS_SOAP_ENV, 'SOAP-ENV:mustUnderstand', '1');
        $response->setAttributeNS(C::NS_SOAP_ENV, 'SOAP-ENV:actor', C::SOAP_ACTOR_NEXT);
        $response->setAttribute('AssertionConsumerServiceURL', $this->getAssertionConsumerServiceURL()->getValue());

        return $response;
    }
}
