<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SOAP\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\StringElementTrait;

/**
 * Class representing the ECP RelayState element.
 *
 * @package simplesamlphp/saml2
 */
final class RelayState extends AbstractEcpElement
{
    use StringElementTrait;

    /**
     * Create a ECP RelayState element.
     *
     * @param string $relayState
     */
    public function __construct(
        string $relayState,
    ) {
        $this->setContent($relayState);
    }


    /**
     * Convert XML into a RelayState
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
        Assert::same($xml->localName, 'RelayState', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RelayState::NS, InvalidDOMElementException::class);

        // Assert required attributes
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV_11, 'actor'),
            'Missing env:actor attribute in <ecp:RelayState>.',
            MissingAttributeException::class,
        );
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand'),
            'Missing env:mustUnderstand attribute in <ecp:RelayState>.',
            MissingAttributeException::class,
        );

        $mustUnderstand = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand');
        Assert::same(
            $mustUnderstand,
            '1',
            'Invalid value of env:mustUnderstand attribute in <ecp:RelayState>.',
            ProtocolViolationException::class,
        );

        $actor = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'actor');
        Assert::same(
            $actor,
            C::SOAP_ACTOR_NEXT,
            'Invalid value of env:actor attribute in <ecp:RelayState>.',
            ProtocolViolationException::class,
        );

        return new static($xml->textContent);
    }


    /**
     * Convert this ECP RelayState to XML.
     *
     * @param \DOMElement|null $parent The element we should append this element to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttributeNS(C::NS_SOAP_ENV_11, 'env:mustUnderstand', '1');
        $e->setAttributeNS(C::NS_SOAP_ENV_11, 'env:actor', C::SOAP_ACTOR_NEXT);
        $e->textContent = $this->getContent();

        return $e;
    }
}
