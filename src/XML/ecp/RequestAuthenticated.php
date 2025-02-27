<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SOAP\Constants as C;
use SimpleSAML\XML\Exception\{InvalidDOMElementException, MissingAttributeException};
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\Type\BooleanValue;

use function intval;
use function strval;

/**
 * Class representing the ECP RequestAuthenticated element.
 *
 * @package simplesamlphp/saml2
 */
final class RequestAuthenticated extends AbstractEcpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Create a ECP RequestAuthenticated element.
     *
     * @param \SimpleSAML\XML\Type\BooleanValue|null $mustUnderstand
     */
    public function __construct(
        protected ?BooleanValue $mustUnderstand,
    ) {
    }


    /**
     * Collect the value of the mustUnderstand-property
     *
     * @return \SimpleSAML\XML\Type\BooleanValue|null
     */
    public function getMustUnderstand(): ?BooleanValue
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
            $mustUnderstand = BooleanValue::fromString($xml->getAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand'));
        }

        Assert::same(
            $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'actor'),
            C::SOAP_ACTOR_NEXT,
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
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getMustUnderstand() !== null) {
            $e->setAttributeNS(
                C::NS_SOAP_ENV_11,
                'env:mustUnderstand',
                strval(intval($this->getMustUnderstand()->getValue())),
            );
        }

        $e->setAttributeNS(C::NS_SOAP_ENV_11, 'env:actor', C::SOAP_ACTOR_NEXT);

        return $e;
    }
}
