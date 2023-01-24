<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SOAP\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;

use function is_null;
use function is_numeric;
use function strval;

/**
 * Class representing the ECP RequestAuthenticated element.
 *
 * @package simplesamlphp/saml2
 */
final class RequestAuthenticated extends AbstractEcpElement
{
    /**
     * Create a ECP RequestAuthenticated element.
     *
     * @param int|null $mustUnderstand
     */
    public function __construct(
        protected ?int $mustUnderstand,
    ) {
        Assert::oneOf(
            $mustUnderstand,
            [null, 0, 1],
            'Invalid value of env:mustUnderstand attribute in <ecp:Response>.',
            ProtocolViolationException::class,
        );
    }


    /**
     * Collect the value of the mustUnderstand-property
     *
     * @return int|null
     */
    public function getMustUnderstand(): ?int
    {
        return $this->mustUnderstand;
    }


    /**
     * Convert XML into a RequestAuthenticated
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'RequestAuthenticated', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RequestAuthenticated::NS, InvalidDOMElementException::class);

        // Assert required attributes
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV_11, 'actor'),
            'Missing env:actor attribute in <ecp:RequestAuthenticated>.',
            MissingAttributeException::class
        );

        $mustUnderstand = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand');
        $actor = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'actor');

        Assert::oneOf(
            $mustUnderstand,
            ['', '0', '1'],
            'Invalid value of env:mustUnderstand attribute in <ecp:Response>.',
            ProtocolViolationException::class,
        );
        Assert::same(
            $actor,
            'http://schemas.xmlsoap.org/soap/actor/next',
            'Invalid value of env:actor attribute in <ecp:Response>.',
            ProtocolViolationException::class,
        );

        $mustUnderstand = ($mustUnderstand === '') ? null : intval($mustUnderstand);

        return new static($mustUnderstand);
    }


    /**
     * Convert this ECP RequestAuthentication to XML.
     *
     * @param \DOMElement|null $parent The element we should append this element to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $response = $this->instantiateParentElement($parent);

        if ($this->getMustUnderstand() !== null) {
            $response->setAttributeNS(C::NS_SOAP_ENV_11, 'env:mustUnderstand', strval($this->getMustUnderstand()));
        }
        $response->setAttributeNS(C::NS_SOAP_ENV_11, 'env:actor', 'http://schemas.xmlsoap.org/soap/actor/next');

        return $response;
    }
}
