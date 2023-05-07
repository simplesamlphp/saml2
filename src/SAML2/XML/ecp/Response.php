<?php

declare(strict_types=1);

namespace SAML2\XML\ecp;

use DOMElement;
use SAML2\Exception\ProtocolViolationException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SOAP\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;

use function filter_var;

/**
 * Class representing the ECP Response element.
 *
 * @package simplesamlphp/saml2
 */
final class Response extends AbstractEcpElement
{
    /**
     * Create a ECP Response element.
     *
     * @param string $assertionConsumerServiceURL
     */
    public function __construct(
        protected string $assertionConsumerServiceURL,
    ) {
        Assert::validURI($assertionConsumerServiceURL, SchemaViolationException::class); // Covers the empty string
    }


    /**
     * Collect the value of the AssertionConsumerServiceURL-property
     *
     * @return string
     */
    public function getAssertionConsumerServiceURL(): string
    {
        return $this->assertionConsumerServiceURL;
    }


    /**
     * Convert XML into a Response
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Response', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Response::NS, InvalidDOMElementException::class);

        // Assert required attributes
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand'),
            'Missing env:mustUnderstand attribute in <ecp:Response>.',
            MissingAttributeException::class,
        );
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV_11, 'actor'),
            'Missing env:actor attribute in <ecp:Response>.',
            MissingAttributeException::class,
        );
        Assert::true(
            $xml->hasAttribute('AssertionConsumerServiceURL'),
            'Missing AssertionConsumerServiceURL attribute in <ecp:Response>.',
            MissingAttributeException::class,
        );

        $mustUnderstand = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand');
        $actor = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'actor');

        Assert::same(
            $mustUnderstand,
            '1',
            'Invalid value of env:mustUnderstand attribute in <ecp:Response>.',
            ProtocolViolationException::class,
        );
        Assert::same(
            $actor,
            'http://schemas.xmlsoap.org/soap/actor/next',
            'Invalid value of env:actor attribute in <ecp:Response>.',
            ProtocolViolationException::class,
        );

        return new static(self::getAttribute($xml, 'AssertionConsumerServiceURL'));
    }


    /**
     * Convert this ECP Response to XML.
     *
     * @param \DOMElement|null $parent The element we should append this element to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $response = $this->instantiateParentElement($parent);

        $response->setAttributeNS(C::NS_SOAP_ENV_11, 'env:mustUnderstand', '1');
        $response->setAttributeNS(C::NS_SOAP_ENV_11, 'env:actor', 'http://schemas.xmlsoap.org/soap/actor/next');
        $response->setAttribute('AssertionConsumerServiceURL', $this->getAssertionConsumerServiceURL());

        return $response;
    }
}
