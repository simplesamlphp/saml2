<?php

declare(strict_types=1);

namespace SAML2\XML\ecp;

use DOMElement;
use InvalidArgumentException;
use SAML2\Constants;
use Webmozart\Assert\Assert;

/**
 * Class representing the ECP Response element.
 */
final class Response extends AbstractEcpElement
{
    /**
     * The AssertionConsumerServiceURL.
     *
     * @var string
     */
    protected $AssertionConsumerServiceURL;


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
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getAssertionConsumerServiceURL(): string
    {
        return $this->AssertionConsumerServiceURL;
    }


    /**
     * Set the value of the AssertionConsumerServiceURL-property
     *
     * @param string $assertionConsumerServiceURL
     * @throws InvalidArgumentException
     * @return void
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
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Response');
        Assert::same($xml->namespaceURI, Response::NS);

        Assert::true(
            $xml->hasAttributeNS(Constants::NS_SOAP, 'mustUnderstand'),
            'Missing SOAP-ENV:mustUnderstand attribute in <ecp:Response>.'
        );
        Assert::true(
            $xml->hasAttributeNS(Constants::NS_SOAP, 'actor'),
            'Missing SOAP-ENV:actor attribute in <ecp:Response>.'
        );
        Assert::true(
            $xml->hasAttribute('AssertionConsumerServiceURL'),
            'Missing AssertionConsumerServiceURL attribute in <ecp:Response>.'
        );

        $mustUnderstand = $xml->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand');
        $actor = $xml->getAttributeNS(Constants::NS_SOAP, 'actor');

        Assert::same($mustUnderstand, '1', 'Invalid value of SOAP-ENV:mustUnderstand attribute in <ecp:Response>.');
        Assert::same(
            $actor,
            'http://schemas.xmlsoap.org/soap/actor/next',
            'Invalid value of SOAP-ENV:actor attribute in <ecp:Response>.'
        );

        return new self($xml->getAttribute('AssertionConsumerServiceURL'));
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
