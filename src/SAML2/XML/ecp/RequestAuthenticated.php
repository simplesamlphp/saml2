<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\Exception\MissingAttributeException;

/**
 * Class representing the ECP RequestAuthenticated element.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class RequestAuthenticated extends AbstractEcpElement
{
    /** @var int|null $mustUnderstand */
    protected $mustUnderstand = null;


    /**
     * Create a ECP RequestAuthenticated element.
     *
     * @param int $mustUnderstand
     * @return void
     */
    public function __construct(?int $mustUnderstand = null)
    {
        $this->mustUnderstand = $mustUnderstand;
    }


    /**
     * Convert XML into a RequestAuthenticated
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\SAML2\Exception\MissingAttributeException if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'RequestAuthenticated', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RequestAuthenticated::NS, InvalidDOMElementException::class);

        // Assert required attributes
        Assert::true(
            $xml->hasAttributeNS(Constants::NS_SOAP, 'actor'),
            'Missing SOAP-ENV:actor attribute in <ecp:RequestAuthenticated>.',
            MissingAttributeException::class
        );

        $mustUnderstand = $xml->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand');
        $actor = $xml->getAttributeNS(Constants::NS_SOAP, 'actor');

        Assert::oneOf($mustUnderstand, ['', '0', '1'], 'Invalid value of SOAP-ENV:mustUnderstand attribute in <ecp:Response>.');
        Assert::same(
            $actor,
            'http://schemas.xmlsoap.org/soap/actor/next',
            'Invalid value of SOAP-ENV:actor attribute in <ecp:Response>.'
        );

        $mustUnderstand = is_numeric($mustUnderstand) ? intval($mustUnderstand) : null;

        return new self($mustUnderstand);
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

        if (!is_null($this->mustUnderstand)) {
            $response->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:mustUnderstand', strval($this->mustUnderstand));
        }
        $response->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:actor', 'http://schemas.xmlsoap.org/soap/actor/next');

        return $response;
    }
}
