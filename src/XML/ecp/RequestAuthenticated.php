<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SOAP\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;

use function boolval;
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
     * @param bool|null $mustUnderstand
     */
    public function __construct(
        protected ?bool $mustUnderstand = false,
    ) {
    }


    /**
     * Collect the value of the mustUnderstand-property
     *
     * @return bool|null
     */
    public function getMustUnderstand(): ?bool
    {
        return $this->mustUnderstand;
    }


    /**
     * Convert XML into a RequestAuthenticated
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
        Assert::same($xml->localName, 'RequestAuthenticated', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RequestAuthenticated::NS, InvalidDOMElementException::class);

        // Assert required attributes
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV_11, 'actor'),
            'Missing env:actor attribute in <ecp:RequestAuthenticated>.',
            MissingAttributeException::class,
        );

        $mustUnderstand = null;
        if ($xml->hasAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand')) {
            $mustUnderstand = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand');

            Assert::nullOrOneOf(
               $mustUnderstand,
                ['0', '1'],
                'Invalid value of env:mustUnderstand attribute in <ecp:RequestAuthenticated>.',
                ProtocolViolationException::class,
            );

            $mustUnderstand = boolval($mustUnderstand);
        }

        $actor = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'actor');
        Assert::same(
            $actor,
            'http://schemas.xmlsoap.org/soap/actor/next',
            'Invalid value of env:actor attribute in <ecp:RequestAuthenticated>.',
            ProtocolViolationException::class,
        );

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
        $e = $this->instantiateParentElement($parent);

        if ($this->getMustUnderstand() !== null) {
            $e->setAttributeNS(C::NS_SOAP_ENV_11, 'env:mustUnderstand', strval(intval($this->getMustUnderstand())));
        }

        $e->setAttributeNS(C::NS_SOAP_ENV_11, 'env:actor', C::SOAP_ACTOR_NEXT);

        return $e;
    }
}
