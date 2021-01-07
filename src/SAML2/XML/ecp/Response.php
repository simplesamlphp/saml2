<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMElement;
use InvalidArgumentException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;

/**
 * Class representing the ECP Response element.
 *
 * @package simplesamlphp/saml2
 */
final class Response extends AbstractEcpElement
{
    /**
     * The AssertionConsumerServiceURL.
     *
     * @var string
     */
    protected string $AssertionConsumerServiceURL;


    /**
     * Create a ECP Response element.
     *
     * @param string $assertionConsumerServiceURL
     */
    public function __construct(string $assertionConsumerServiceURL)
    {
        $this->setAssertionConsumerServiceURL($assertionConsumerServiceURL);
    }


    /**
     * Collect the value of the AssertionConsumerServiceURL-property
     *
     * @return string
     */
    public function getAssertionConsumerServiceURL(): string
    {
        return $this->AssertionConsumerServiceURL;
    }


    /**
     * Set the value of the AssertionConsumerServiceURL-property
     *
     * @param string $assertionConsumerServiceURL
     * @throws \InvalidArgumentException if provided string is not a valid URL
     */
    private function setAssertionConsumerServiceURL(string $assertionConsumerServiceURL): void
    {
        if (!filter_var($assertionConsumerServiceURL, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('AssertionConsumerServiceURL is not a valid URL.');
        }

        $this->AssertionConsumerServiceURL = $assertionConsumerServiceURL;
    }


    /**
     * Convert XML into a Response
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Response', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Response::NS, InvalidDOMElementException::class);

        // Assert required attributes
        Assert::true(
            $xml->hasAttributeNS(Constants::NS_SOAP, 'mustUnderstand'),
            'Missing SOAP-ENV:mustUnderstand attribute in <ecp:Response>.',
            MissingAttributeException::class
        );
        Assert::true(
            $xml->hasAttributeNS(Constants::NS_SOAP, 'actor'),
            'Missing SOAP-ENV:actor attribute in <ecp:Response>.',
            MissingAttributeException::class
        );
        Assert::true(
            $xml->hasAttribute('AssertionConsumerServiceURL'),
            'Missing AssertionConsumerServiceURL attribute in <ecp:Response>.',
            MissingAttributeException::class
        );

        $mustUnderstand = $xml->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand');
        $actor = $xml->getAttributeNS(Constants::NS_SOAP, 'actor');

        Assert::same($mustUnderstand, '1', 'Invalid value of SOAP-ENV:mustUnderstand attribute in <ecp:Response>.');
        Assert::same(
            $actor,
            'http://schemas.xmlsoap.org/soap/actor/next',
            'Invalid value of SOAP-ENV:actor attribute in <ecp:Response>.'
        );

        return new self(self::getAttribute($xml, 'AssertionConsumerServiceURL'));
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

        $response->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:mustUnderstand', '1');
        $response->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:actor', 'http://schemas.xmlsoap.org/soap/actor/next');
        $response->setAttribute('AssertionConsumerServiceURL', $this->AssertionConsumerServiceURL);

        return $response;
    }
}
