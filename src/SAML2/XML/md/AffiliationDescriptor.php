<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\Exception\TooManyElementsException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\ds\Signature;

/**
 * Class representing SAML 2 AffiliationDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
final class AffiliationDescriptor extends AbstractMetadataDocument
{
    /**
     * The affiliationOwnerID.
     *
     * @var string
     */
    public $affiliationOwnerID;

    /**
     * The AffiliateMember(s).
     *
     * Array of entity ID strings.
     *
     * @var string[]
     */
    protected $AffiliateMembers = [];

    /**
     * KeyDescriptor elements.
     *
     * Array of \SimpleSAML\SAML2\XML\md\KeyDescriptor elements.
     *
     * @var \SimpleSAML\SAML2\XML\md\KeyDescriptor[]
     */
    protected $KeyDescriptors = [];


    /**
     * Generic constructor for SAML metadata documents.
     *
     * @param string $ownerID The ID of the owner of this affiliation.
     * @param array $members A non-empty array of members of this affiliation.
     * @param string|null $ID The ID for this document. Defaults to null.
     * @param int|null $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An array of extensions. Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptors An optional array of KeyDescriptors. Defaults to an empty array.
     */
    public function __construct(
        string $ownerID,
        array $members,
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        array $keyDescriptors = []
    ) {
        parent::__construct($ID, $validUntil, $cacheDuration, $extensions);
        $this->setAffiliationOwnerID($ownerID);
        $this->setAffiliateMembers($members);
        $this->setKeyDescriptors($keyDescriptors);
    }


    /**
     * Initialize a AffiliationDescriptor.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return \SimpleSAML\SAML2\XML\md\AffiliationDescriptor
     *
     * @throws \SimpleSAML\SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\SAML2\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\SAML2\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AffiliationDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AffiliationDescriptor::NS, InvalidDOMElementException::class);

        $owner = self::getAttribute($xml, 'affiliationOwnerID');
        $members = Utils::extractStrings($xml, Constants::NS_MD, 'AffiliateMember');
        $keyDescriptors = KeyDescriptor::getChildrenOfClass($xml);

        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor', TooManyElementsException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.', TooManyElementsException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $afd = new self(
            $owner,
            $members,
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? Utils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            $keyDescriptors
        );
        if (!empty($signature)) {
            $afd->setSignature($signature[0]);
        }
        return $afd;
    }


    /**
     * Collect the value of the affiliationOwnerId-property
     *
     * @return string
     */
    public function getAffiliationOwnerID(): string
    {
        return $this->affiliationOwnerID;
    }


    /**
     * Set the value of the affiliationOwnerId-property
     *
     * @param string $affiliationOwnerId
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setAffiliationOwnerID(string $affiliationOwnerId): void
    {
        Assert::notEmpty($affiliationOwnerId, 'AffiliationOwnerID must not be empty.');
        $this->affiliationOwnerID = $affiliationOwnerId;
    }


    /**
     * Collect the value of the AffiliateMember-property
     *
     * @return array
     */
    public function getAffiliateMembers(): array
    {
        return $this->AffiliateMembers;
    }


    /**
     * Set the value of the AffiliateMember-property
     *
     * @param string[] $affiliateMembers
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setAffiliateMembers(array $affiliateMembers): void
    {
        Assert::notEmpty($affiliateMembers, 'List of affiliated members must not be empty.');
        Assert::allStringNotEmpty(
            $affiliateMembers,
            'Cannot specify an empty string as an affiliation member entityID.'
        );
        $this->AffiliateMembers = $affiliateMembers;
    }


    /**
     * Collect the value of the KeyDescriptor-property
     *
     * @return \SimpleSAML\SAML2\XML\md\KeyDescriptor[]
     */
    public function getKeyDescriptors(): array
    {
        return $this->KeyDescriptors;
    }


    /**
     * Set the value of the KeyDescriptor-property
     *
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptors
     * @return void
     */
    protected function setKeyDescriptors(array $keyDescriptors): void
    {
        Assert::allIsInstanceOf($keyDescriptors, KeyDescriptor::class);
        $this->KeyDescriptors = $keyDescriptors;
    }


    /**
     * Add this AffiliationDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement|null $parent The EntityDescriptor we should append this endpoint to.
     * @return \DOMElement
     * @throws \Exception
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        $e->setAttribute('affiliationOwnerID', $this->affiliationOwnerID);
        Utils::addStrings($e, Constants::NS_MD, 'md:AffiliateMember', false, $this->AffiliateMembers);

        foreach ($this->KeyDescriptors as $kd) {
            $kd->toXML($e);
        }

        return $this->signElement($e);
    }
}
