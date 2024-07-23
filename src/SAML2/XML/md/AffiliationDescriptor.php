<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\XsNamespace as NS;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

/**
 * Class representing SAML 2 AffiliationDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
final class AffiliationDescriptor extends AbstractMetadataDocument
{
    use ExtendableAttributesTrait;

    /** The namespace-attribute for the xs:anyAttribute element */
    public const XS_ANY_ATTR_NAMESPACE = NS::OTHER;


    /**
     * Generic constructor for SAML metadata documents.
     *
     * @param string $affiliationOwnerId The ID of the owner of this affiliation.
     * @param \SimpleSAML\SAML2\XML\md\AffiliateMember[] $affiliateMember
     *   A non-empty array of members of this affiliation.
     * @param string|null $ID The ID for this document. Defaults to null.
     * @param \DateTimeImmutable|null $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An array of extensions. Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptor
     *   An optional array of KeyDescriptors. Defaults to an empty array.
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttribute
     */
    public function __construct(
        protected string $affiliationOwnerId,
        protected array $affiliateMember,
        ?string $ID = null,
        ?DateTimeImmutable $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        protected array $keyDescriptor = [],
        array $namespacedAttribute = [],
    ) {
        SAMLAssert::validURI($affiliationOwnerId);
        Assert::maxLength(
            $affiliationOwnerId,
            C::ENTITYID_MAX_LENGTH,
            sprintf('The AffiliationOwnerID attribute cannot be longer than %d characters.', C::ENTITYID_MAX_LENGTH),
            ProtocolViolationException::class,
        );
        Assert::notEmpty($affiliateMember, 'List of affiliated members must not be empty.');
        Assert::maxCount($affiliateMember, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($affiliateMember, AffiliateMember::class);
        Assert::maxCount($keyDescriptor, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($keyDescriptor, KeyDescriptor::class);

        parent::__construct($ID, $validUntil, $cacheDuration, $extensions);

        $this->setAttributesNS($namespacedAttribute);
    }


    /**
     * Collect the value of the affiliationOwnerId-property
     *
     * @return string
     */
    public function getAffiliationOwnerId(): string
    {
        return $this->affiliationOwnerId;
    }


    /**
     * Collect the value of the AffiliateMember-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AffiliateMember[]
     */
    public function getAffiliateMember(): array
    {
        return $this->affiliateMember;
    }


    /**
     * Collect the value of the KeyDescriptor-property
     *
     * @return \SimpleSAML\SAML2\XML\md\KeyDescriptor[]
     */
    public function getKeyDescriptor(): array
    {
        return $this->keyDescriptor;
    }


    /**
     * Initialize a AffiliationDescriptor.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AffiliationDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AffiliationDescriptor::NS, InvalidDOMElementException::class);

        $owner = self::getAttribute($xml, 'affiliationOwnerID');
        $members = AffiliateMember::getChildrenOfClass($xml);
        $keyDescriptors = KeyDescriptor::getChildrenOfClass($xml);

        $validUntil = self::getOptionalAttribute($xml, 'validUntil', null);
        SAMLAssert::nullOrValidDateTime($validUntil);

        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount(
            $orgs,
            1,
            'More than one Organization found in this descriptor',
            TooManyElementsException::class,
        );

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one md:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount(
            $signature,
            1,
            'Only one ds:Signature element is allowed.',
            TooManyElementsException::class,
        );

        $afd = new static(
            $owner,
            $members,
            self::getOptionalAttribute($xml, 'ID', null),
            $validUntil !== null ? new DateTimeImmutable($validUntil) : null,
            self::getOptionalAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            $keyDescriptors,
            self::getAttributesNSFromXML($xml),
        );

        if (!empty($signature)) {
            $afd->setSignature($signature[0]);
            $afd->setXML($xml);
        }

        return $afd;
    }


    /**
     * Convert this assertion to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    public function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);
        $e->setAttribute('affiliationOwnerID', $this->getAffiliationOwnerId());

        foreach ($this->getAttributesNS() as $attr) {
            $attr->toXML($e);
        }

        foreach ($this->getAffiliateMember() as $am) {
            $am->toXML($e);
        }

        foreach ($this->getKeyDescriptor() as $kd) {
            $kd->toXML($e);
        }

        return $e;
    }
}
