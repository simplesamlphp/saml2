<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XMLSchema\Type\NCNameValue;
use SimpleSAML\XMLSchema\XML\AbstractAnyType;
use SimpleSAML\XMLSchema\XML\Constants\NS;

use function strval;

/**
 * Abstract class representing SAML 2 SubjectConfirmationData element.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractSubjectConfirmationData extends AbstractAnyType
{
    public const string NS = C::NS_SAML;

    public const string NS_PREFIX = 'saml';

    public const string SCHEMA = 'resources/schemas/saml-schema-assertion-2.0.xsd';

    /** The namespace-attribute for the xs:any element */
    public const string XS_ANY_ELT_NAMESPACE = NS::ANY;

    /** The namespace-attribute for the xs:anyAttribute element */
    public const string XS_ANY_ATTR_NAMESPACE = NS::OTHER;

    /**
     * The exclusions for the xs:anyAttribute element
     *
     * @var array<int, array<int, string>>
     */
    public const array XS_ANY_ATTR_EXCLUSIONS = [
        ['urn:oasis:names:tc:SAML:2.0:assertion', '*'],
        ['urn:oasis:names:tc:SAML:2.0:protocol', '*'],
    ];


    /**
     * Initialize (and parse) a SubjectConfirmationData element.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $notBefore
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $notOnOrAfter
     * @param \SimpleSAML\SAML2\Type\EntityIDValue|null $recipient
     * @param \SimpleSAML\XMLSchema\Type\NCNameValue|null $inResponseTo
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
        /** SAML 2.0 Core specifications paragraph 2.4.1.2 */
        if ($notBefore !== null && $notOnOrAfter !== null) {
            Assert::true(
                $notBefore->toDateTime() < $notOnOrAfter->toDateTime(),
                "The value for NotBefore MUST be less than (earlier than) the value for NotOnOrAfter.",
                ProtocolViolationException::class,
            );
        }

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
     * @return \SimpleSAML\XMLSchema\Type\NCNameValue|null
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
     * Convert this element to XML.
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
