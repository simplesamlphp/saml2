<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\{SAMLDateTimeValue, EntityIDValue, SAMLStringValue};
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\{ExtendableAttributesTrait, ExtendableElementTrait};
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\Type\NCNameValue;
use SimpleSAML\XML\XsNamespace as NS;

use function strval;

/**
 * Class representing SAML 2 SubjectConfirmationData element.
 *
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationData extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use ExtendableAttributesTrait;
    use ExtendableElementTrait;
    use SchemaValidatableElementTrait;


    /** The namespace-attribute for the xs:any element */
    public const XS_ANY_ELT_NAMESPACE = NS::ANY;

    /** The namespace-attribute for the xs:anyAttribute element */
    public const XS_ANY_ATTR_NAMESPACE = NS::OTHER;


    /**
     * Initialize (and parse) a SubjectConfirmationData element.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $notBefore
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $notOnOrAfter
     * @param \SimpleSAML\SAML2\Type\EntityIDValue|null $recipient
     * @param \SimpleSAML\XML\Type\NCNameValue|null $inResponseTo
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $address
     * @param \SimpleSAML\XML\SerializableElementInterface[] $children
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    public function __construct(
        protected ?SAMLDateTimeValue $notBefore = null,
        protected ?SAMLDateTimeValue $notOnOrAfter = null,
        protected ?EntityIDValue $recipient = null,
        protected ?NCNameValue $inResponseTo = null,
        protected ?SAMLStringValue $address = null,
        array $children = [],
        array $namespacedAttributes = [],
    ) {
        if ($address !== null) {
            try {
                /**
                 * IPv4 addresses SHOULD be represented in the usual dotted-decimal format (e.g., "1.2.3.4").
                 * IPv6 addresses SHOULD be represented as defined by Section 2.2 of IETF RFC 3513 [RFC 3513]
                 * (e.g., "FEDC:BA98:7654:3210:FEDC:BA98:7654:3210").
                 */
                Assert::ip($address->getValue());
            } catch (AssertionFailedException) {
                Utils::getContainer()->getLogger()->warning(
                    sprintf('Provided address (%s) is not a valid IPv4 or IPv6  address.', $address->getValue()),
                );
            }
        }

        $this->setElements($children);
        $this->setAttributesNS($namespacedAttributes);
    }


    /**
     * Collect the value of the NotBefore-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null
     */
    public function getNotBefore(): ?SAMLDateTimeValue
    {
        return $this->notBefore;
    }


    /**
     * Collect the value of the NotOnOrAfter-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null
     */
    public function getNotOnOrAfter(): ?SAMLDateTimeValue
    {
        return $this->notOnOrAfter;
    }


    /**
     * Collect the value of the Recipient-property
     *
     * @return \SimpleSAML\SAML2\Type\EntityIDValue|null
     */
    public function getRecipient(): ?EntityIDValue
    {
        return $this->recipient;
    }


    /**
     * Collect the value of the InResponseTo-property
     *
     * @return \SimpleSAML\XML\Type\NCNameValue|null
     */
    public function getInResponseTo(): ?NCNameValue
    {
        return $this->inResponseTo;
    }


    /**
     * Collect the value of the Address-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getAddress(): ?SAMLStringValue
    {
        return $this->address;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return empty($this->getNotBefore())
            && empty($this->getNotOnOrAfter())
            && empty($this->getRecipient())
            && empty($this->getInResponseTo())
            && empty($this->getAddress())
            && empty($this->getElements())
            && empty($this->getAttributesNS());
    }


    /**
     * Convert XML into a SubjectConfirmationData
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     * @throws \SimpleSAML\Assert\AssertionFailedException
     *   if NotBefore or NotOnOrAfter contain an invalid date.
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'SubjectConfirmationData', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SubjectConfirmationData::NS, InvalidDOMElementException::class);

        return new static(
            self::getOptionalAttribute($xml, 'NotBefore', SAMLDateTimeValue::class, null),
            self::getOptionalAttribute($xml, 'NotOnOrAfter', SAMLDateTimeValue::class, null),
            self::getOptionalAttribute($xml, 'Recipient', EntityIDValue::class, null),
            self::getOptionalAttribute($xml, 'InResponseTo', NCNameValue::class, null),
            self::getOptionalAttribute($xml, 'Address', SAMLStringValue::class, null),
            self::getChildElementsFromXML($xml),
            self::getAttributesNSFromXML($xml),
        );
    }


    /**
     * Convert this element to XML.
     *
     * @param  \DOMElement|null $parent The parent element we should append this element to.
     * @return \DOMElement This element, as XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getNotBefore() !== null) {
            $e->setAttribute('NotBefore', strval($this->getNotBefore()));
        }
        if ($this->getNotOnOrAfter() !== null) {
            $e->setAttribute('NotOnOrAfter', strval($this->getNotOnOrAfter()));
        }
        if ($this->getRecipient() !== null) {
            $e->setAttribute('Recipient', strval($this->getRecipient()));
        }
        if ($this->getInResponseTo() !== null) {
            $e->setAttribute('InResponseTo', strval($this->getInResponseTo()));
        }
        if ($this->getAddress() !== null) {
            $e->setAttribute('Address', strval($this->getAddress()));
        }

        foreach ($this->getAttributesNS() as $attr) {
            $attr->toXML($e);
        }

        foreach ($this->getElements() as $n) {
            $n->toXML($e);
        }

        return $e;
    }
}
