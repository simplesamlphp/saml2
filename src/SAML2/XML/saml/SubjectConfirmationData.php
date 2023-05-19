<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;

use function filter_var;
use function gmdate;
use function is_null;

/**
 * Class representing SAML 2 SubjectConfirmationData element.
 *
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationData extends AbstractSamlElement
{
    use ExtendableAttributesTrait;

    /** The namespace-attribute for the xs:anyAttribute element */
    public const XS_ANY_ATTR_NAMESPACE = C::XS_ANY_NS_OTHER;


    /**
     * Initialize (and parse) a SubjectConfirmationData element.
     *
     * @param int|null $notBefore
     * @param int|null $notOnOrAfter
     * @param string|null $recipient
     * @param string|null $inResponseTo
     * @param string|null $address
     * @param (\SimpleSAML\XMLSecurity\XML\ds\KeyInfo|\SimpleSAML\XML\Chunk)[] $info
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    public function __construct(
        protected ?int $notBefore = null,
        protected ?int $notOnOrAfter = null,
        protected ?string $recipient = null,
        protected ?string $inResponseTo = null,
        protected ?string $address = null,
        protected array $info = [],
        array $namespacedAttributes = [],
    ) {
        Assert::nullOrNotWhitespaceOnly($recipient);
        Assert::nullOrValidNCName($inResponseTo); // Covers the empty string

        if (!is_null($address) && !filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            Utils::getContainer()->getLogger()->warning(
                sprintf('Provided argument (%s) is not a valid IP address.', $address),
            );
        }

        Assert::allIsInstanceOfAny($info, [Chunk::class, KeyInfo::class]);

        $this->setAttributesNS($namespacedAttributes);
    }


    /**
     * Collect the value of the NotBefore-property
     *
     * @return int|null
     */
    public function getNotBefore(): ?int
    {
        return $this->notBefore;
    }


    /**
     * Collect the value of the NotOnOrAfter-property
     *
     * @return int|null
     */
    public function getNotOnOrAfter(): ?int
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
     * Collect the value of the info-property
     *
     * @return (\SimpleSAML\XMLSecurity\XML\ds\KeyInfo|\SimpleSAML\XML\Chunk)[]
     */
    public function getInfo(): array
    {
        return $this->info;
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
            && empty($this->info)
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
            $NotBefore = XMLUtils::xsDateTimeToTimestamp($NotBefore);
        }

        $NotOnOrAfter = self::getOptionalAttribute($xml, 'NotOnOrAfter', null);
        if ($NotOnOrAfter !== null) {
            // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
            $NotOnOrAfter = preg_replace('/([.][0-9]+Z)$/', 'Z', $NotOnOrAfter, 1);

            Assert::validDateTimeZulu($NotOnOrAfter, ProtocolViolationException::class);
            $NotOnOrAfter = XMLUtils::xsDateTimeToTimestamp($NotOnOrAfter);
        }

        $Recipient = self::getOptionalAttribute($xml, 'Recipient', null);
        $InResponseTo = self::getOptionalAttribute($xml, 'InResponseTo', null);
        $Address = self::getOptionalAttribute($xml, 'Address', null);

        $info = [];
        foreach ($xml->childNodes as $n) {
            if (!($n instanceof DOMElement)) {
                continue;
            } elseif ($n->namespaceURI === C::NS_XDSIG && $n->localName === 'KeyInfo') {
                $info[] = KeyInfo::fromXML($n);
                continue;
            } else {
                $info[] = new Chunk($n);
                continue;
            }
        }

        return new static(
            $NotBefore,
            $NotOnOrAfter,
            $Recipient,
            $InResponseTo,
            $Address,
            $info,
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
            $e->setAttribute('NotBefore', gmdate('Y-m-d\TH:i:s\Z', $this->getNotBefore()));
        }
        if ($this->getNotOnOrAfter() !== null) {
            $e->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->getNotOnOrAfter()));
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

        foreach ($this->getInfo() as $n) {
            $n->toXML($e);
        }

        return $e;
    }
}
