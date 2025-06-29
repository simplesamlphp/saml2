<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SOAP\Constants as C;
use SimpleSAML\XML\Exception\{InvalidDOMElementException, MissingAttributeException};
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class representing the ECP RelayState element.
 *
 * @package simplesamlphp/saml2
 */
final class RelayState extends AbstractEcpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;

    /** @var string */
    public const TEXTCONTENT_TYPE = SAMLStringValue::class;


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

        Assert::same(
            $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand'),
            '1',
            'Invalid value of env:mustUnderstand attribute in <ecp:RelayState>.',
            ProtocolViolationException::class,
        );

        Assert::same(
            $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'actor'),
            C::SOAP_ACTOR_NEXT,
            'Invalid value of env:actor attribute in <ecp:RelayState>.',
            ProtocolViolationException::class,
        );

        return new static(
            SAMLStringValue::fromString($xml->textContent),
        );
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
        $e->textContent = $this->getContent()->getValue();

        return $e;
    }
}
