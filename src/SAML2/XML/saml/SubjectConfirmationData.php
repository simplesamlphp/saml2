<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XML\XsNamespace as NS;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;

use function filter_var;
use function is_null;

/**
 * Class representing SAML 2 SubjectConfirmationData element.
 *
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationData extends AbstractSamlElement
{
    use ExtendableAttributesTrait;
    use ExtendableElementTrait;

    /** The namespace-attribute for the xs:any element */
    public const XS_ANY_ELT_NAMESPACE = NS::ANY;

    /** The namespace-attribute for the xs:anyAttribute element */
    public const XS_ANY_ATTR_NAMESPACE = NS::OTHER;


    /**
     * Initialize (and parse) a SubjectConfirmationData element.
     *
     * @param \DateTimeImmutable|null $notBefore
     * @param \DateTimeImmutable|null $notOnOrAfter
     * @param string|null $recipient
     * @param string|null $inResponseTo
     * @param string|null $address
     * @param \SimpleSAML\XML\SerializableElementInterface[] $children
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    public function __construct(
        protected ?DateTimeImmutable $notBefore = null,
        protected ?DateTimeImmutable $notOnOrAfter = null,
        protected ?string $recipient = null,
        protected ?string $inResponseTo = null,
        protected ?string $address = null,
        array $children = [],
        array $namespacedAttributes = [],
    ) {
        Assert::nullOrSame($notBefore?->getTimeZone()->getName(), 'Z', ProtocolViolationException::class);
        Assert::nullOrSame($notOnOrAfter?->getTimeZone()->getName(), 'Z', ProtocolViolationException::class);
        Assert::nullOrNotWhitespaceOnly($recipient);
        Assert::nullOrValidNCName($inResponseTo); // Covers the empty string

        if (!is_null($address) && !filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            Utils::getContainer()->getLogger()->warning(
                sprintf('Provided argument (%s) is not a valid IP address.', $address),
            );
        }

        $this->setElements($children);
        $this->setAttributesNS($namespacedAttributes);
    }


    /**
     * Collect the value of the NotBefore-property
     *
     * @return \DateTimeImmutable|null
     */
    public function getNotBefore(): ?DateTimeImmutable
    {
        return $this->notBefore;
    }


    /**
     * Collect the value of the NotOnOrAfter-property
     *
     * @return \DateTimeImmutable|null
     */
    public function getNotOnOrAfter(): ?DateTimeImmutable
    {
        return $this->notOnOrAfter;
    }


    /**
     * Collect the value of the Recipient-property
     *
     * @return string|null
     */
    public function getRecipient(): ?string
    {
        return $this->recipient;
    }


    /**
     * Collect the value of the InResponseTo-property
     *
     * @return string|null
     */
    public function getInResponseTo(): ?string
    {
        return $this->inResponseTo;
    }


    /**
     * Collect the value of the Address-property
     *
     * @return string|null
     */
    public function getAddress(): ?string
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
        return empty($this->notBefore)
            && empty($this->notOnOrAfter)
            && empty($this->recipient)
            && empty($this->inResponseTo)
            && empty($this->address)
            && empty($this->elements)
            && empty($this->namespacedAttributes);
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

        $NotBefore = self::getOptionalAttribute($xml, 'NotBefore', null);
        if ($NotBefore !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $NotBefore = preg_replace('/([.][0-9]+Z)$/', 'Z', $NotBefore, 1);

            Assert::validDateTimeZulu($NotBefore, ProtocolViolationException::class);
            $NotBefore = new DateTimeImmutable($NotBefore);
        }

        $NotOnOrAfter = self::getOptionalAttribute($xml, 'NotOnOrAfter', null);
        if ($NotOnOrAfter !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $NotOnOrAfter = preg_replace('/([.][0-9]+Z)$/', 'Z', $NotOnOrAfter, 1);

            Assert::validDateTimeZulu($NotOnOrAfter, ProtocolViolationException::class);
            $NotOnOrAfter = new DateTimeImmutable($NotOnOrAfter);
        }

        $Recipient = self::getOptionalAttribute($xml, 'Recipient', null);
        $InResponseTo = self::getOptionalAttribute($xml, 'InResponseTo', null);
        $Address = self::getOptionalAttribute($xml, 'Address', null);

        $children = [];
        foreach ($xml->childNodes as $n) {
            if (!($n instanceof DOMElement)) {
                continue;
            } elseif ($n->namespaceURI === C::NS_XDSIG && $n->localName === 'KeyInfo') {
                $children[] = KeyInfo::fromXML($n);
                continue;
            } else {
                $children[] = new Chunk($n);
                continue;
            }
        }

        return new static(
            $NotBefore,
            $NotOnOrAfter,
            $Recipient,
            $InResponseTo,
            $Address,
            $children,
            self::getAttributesNSFromXML($xml),
        );
    }


    /**
     * Convert this element to XML.
     *
     * @param  \DOMElement|null $parent The parent element we should append this element to.
     * @return \DOMElement This element, as XML.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if ($this->getNotBefore() !== null) {
            $e->setAttribute('NotBefore', $this->getNotBefore()->format(C::DATETIME_FORMAT));
        }
        if ($this->getNotOnOrAfter() !== null) {
            $e->setAttribute('NotOnOrAfter', $this->getNotOnOrAfter()->format(C::DATETIME_FORMAT));
        }
        if ($this->getRecipient() !== null) {
            $e->setAttribute('Recipient', $this->getRecipient());
        }
        if ($this->getInResponseTo() !== null) {
            $e->setAttribute('InResponseTo', $this->getInResponseTo());
        }
        if ($this->getAddress() !== null) {
            $e->setAttribute('Address', $this->getAddress());
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
