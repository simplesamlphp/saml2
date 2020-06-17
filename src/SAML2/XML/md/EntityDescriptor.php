<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use InvalidArgumentException;
use SAML2\Constants;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Exception\TooManyElementsException;
use SAML2\Utils;
use SAML2\XML\ds\Signature;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SAML 2 EntityDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
final class EntityDescriptor extends AbstractMetadataDocument
{
    /**
     * The entityID this EntityDescriptor represents.
     *
     * @var string
     */
    protected $entityID;

    /**
     * Array with all roles for this entity.
     *
     * Array of \SAML2\XML\md\RoleDescriptor objects (and subclasses of RoleDescriptor).
     *
     * @var \SAML2\XML\md\AbstractRoleDescriptor[]
     */
    protected $RoleDescriptor = [];

    /**
     * AffiliationDescriptor of this entity.
     *
     * @var \SAML2\XML\md\AffiliationDescriptor|null
     */
    protected $AffiliationDescriptor = null;

    /**
     * Organization of this entity.
     *
     * @var \SAML2\XML\md\Organization|null
     */
    protected $Organization = null;

    /**
     * ContactPerson elements for this entity.
     *
     * @var \SAML2\XML\md\ContactPerson[]
     */
    protected $ContactPerson = [];

    /**
     * AdditionalMetadataLocation elements for this entity.
     *
     * @var \SAML2\XML\md\AdditionalMetadataLocation[]
     */
    protected $AdditionalMetadataLocation = [];


    /**
     * Initialize an EntitiyDescriptor.
     *
     * @param string $entityID The entityID of the entity described by this descriptor.
     * @param string|null $id The ID for this document. Defaults to null.
     * @param int|null $validUntil Unix time of validify for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SAML2\XML\md\Extensions|null $extensions An array of extensions.
     * @param \SAML2\XML\md\AbstractRoleDescriptor[] $roleDescriptors An array of role descriptors.
     * @param \SAML2\XML\md\AffiliationDescriptor|null $affiliationDescriptor An affiliation descriptor to
     *   use instead of role descriptors.
     * @param \SAML2\XML\md\Organization|null $organization The organization responsible for the SAML entity.
     * @param \SAML2\XML\md\ContactPerson[] $contacts A list of contact persons for this SAML entity.
     * @param \SAML2\XML\md\AdditionalMetadataLocation[] $additionalMdLocations A list of
     *   additional metadata locations.
     *
     * @throws \Exception
     */
    public function __construct(
        string $entityID,
        ?string $id = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        Extensions $extensions = null,
        array $roleDescriptors = [],
        ?AffiliationDescriptor $affiliationDescriptor = null,
        ?Organization $organization = null,
        array $contacts = [],
        array $additionalMdLocations = []
    ) {
        if (empty($roleDescriptors) && $affiliationDescriptor === null) {
            throw new InvalidArgumentException(
                'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.'
            );
        }

        parent::__construct($id, $validUntil, $cacheDuration, $extensions);

        $this->setEntityID($entityID);
        $this->setRoleDescriptors($roleDescriptors);
        $this->setAffiliationDescriptor($affiliationDescriptor);
        $this->setOrganization($organization);
        $this->setContactPersons($contacts);
        $this->setAdditionalMetadataLocations($additionalMdLocations);
    }


    /**
     * Convert an existing XML into an EntityDescriptor object
     *
     * @param \DOMElement $xml An existing EntityDescriptor XML document.
     * @return \SAML2\XML\md\EntityDescriptor An object representing the given document.
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SAML2\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SAML2\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'EntityDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, EntityDescriptor::NS, InvalidDOMElementException::class);

        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.', TooManyElementsException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $entityID = self::getAttribute($xml, 'entityID');
        $roleDescriptors = [];
        $affiliationDescriptor = null;
        $organization = null;
        $contactPersons = [];
        $additionalMetadataLocation = [];
        foreach ($xml->childNodes as $node) {
            if (
                !($node instanceof DOMElement)
                || ($node->namespaceURI !== Constants::NS_MD)
            ) {
                continue;
            }

            switch ($node->localName) {
                case 'Extensions':
                    continue 2;
                case 'IDPSSODescriptor':
                    $roleDescriptors[] = IDPSSODescriptor::fromXML($node);
                    break;
                case 'SPSSODescriptor':
                    $roleDescriptors[] = SPSSODescriptor::fromXML($node);
                    break;
                case 'AuthnAuthorityDescriptor':
                    $roleDescriptors[] = AuthnAuthorityDescriptor::fromXML($node);
                    break;
                case 'AttributeAuthorityDescriptor':
                    $roleDescriptors[] = AttributeAuthorityDescriptor::fromXML($node);
                    break;
                case 'PDPDescriptor':
                    $roleDescriptors[] = PDPDescriptor::fromXML($node);
                    break;
                case 'AffiliationDescriptor':
                    if ($affiliationDescriptor !== null) {
                        throw new TooManyElementsException('More than one AffiliationDescriptor in the entity.');
                    }
                    $affiliationDescriptor = AffiliationDescriptor::fromXML($node);
                    break;
                case 'Organization':
                    if ($organization !== null) {
                        throw new TooManyElementsException('More than one Organization in the entity.');
                    }
                    $organization = Organization::fromXML($node);
                    break;
                case 'ContactPerson':
                    $contactPersons[] = ContactPerson::fromXML($node);
                    break;
                case 'AdditionalMetadataLocation':
                    $additionalMetadataLocation[] = AdditionalMetadataLocation::fromXML($node);
                    break;
                default:
                    $roleDescriptors[] = UnknownRoleDescriptor::fromXML($node);
            }
        }

        if (empty($roleDescriptors) && is_null($affiliationDescriptor)) {
            throw new InvalidArgumentException(
                'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.'
            );
        } elseif (!empty($roleDescriptors) && !is_null($affiliationDescriptor)) {
            throw new InvalidArgumentException(
                'AffiliationDescriptor cannot be combined with other RoleDescriptor elements in EntityDescriptor.'
            );
        }

        $entity = new self(
            $entityID,
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? Utils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            $roleDescriptors,
            $affiliationDescriptor,
            $organization,
            $contactPersons,
            $additionalMetadataLocation
        );
        if (!empty($signature)) {
            $entity->setSignature($signature[0]);
        }
        return $entity;
    }


    /**
     * Collect the value of the entityID property.
     *
     * @return string
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function getEntityID(): string
    {
        Assert::notEmpty($this->entityID);

        return $this->entityID;
    }


    /**
     * Set the value of the entityID-property
     * @param string $entityId
     * @return void
     */
    protected function setEntityID(string $entityId): void
    {
        Assert::notEmpty($entityId, 'The entityID attribute cannot be empty.');
        Assert::maxLength($entityId, 1024, 'The entityID attribute cannot be longer than 1024 characters.');
        $this->entityID = $entityId;
    }


    /**
     * Collect the value of the RoleDescriptor property.
     *
     * @return \SAML2\XML\md\AbstractRoleDescriptor[]
     */
    public function getRoleDescriptors(): array
    {
        return $this->RoleDescriptor;
    }


    /**
     * Set the value of the RoleDescriptor property.
     *
     * @param \SAML2\XML\md\AbstractRoleDescriptor[] $roleDescriptors
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setRoleDescriptors(array $roleDescriptors): void
    {
        Assert::allIsInstanceOf(
            $roleDescriptors,
            AbstractRoleDescriptor::class,
            'All role descriptors must extend AbstractRoleDescriptor.'
        );
        $this->RoleDescriptor = $roleDescriptors;
    }


    /**
     * Collect the value of the AffiliationDescriptor property.
     *
     * @return \SAML2\XML\md\AffiliationDescriptor|null
     */
    public function getAffiliationDescriptor(): ?AffiliationDescriptor
    {
        return $this->AffiliationDescriptor;
    }


    /**
     * Set the value of the AffliationDescriptor property.
     *
     * @param \SAML2\XML\md\AffiliationDescriptor|null $affiliationDescriptor
     * @return void
     */
    protected function setAffiliationDescriptor(?AffiliationDescriptor $affiliationDescriptor = null): void
    {
        $this->AffiliationDescriptor = $affiliationDescriptor;
    }


    /**
     * Collect the value of the Organization property.
     *
     * @return \SAML2\XML\md\Organization|null
     */
    public function getOrganization(): ?Organization
    {
        return $this->Organization;
    }


    /**
     * Set the value of the Organization property.
     *
     * @param \SAML2\XML\md\Organization|null $organization
     * @return void
     */
    protected function setOrganization(?Organization $organization = null): void
    {
        $this->Organization = $organization;
    }


    /**
     * Collect the value of the ContactPerson property.
     *
     * @return \SAML2\XML\md\ContactPerson[]
     */
    public function getContactPersons(): array
    {
        return $this->ContactPerson;
    }


    /**
     * Set the value of the ContactPerson property.
     *
     * @param array $contactPerson
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setContactPersons(array $contactPerson): void
    {
        Assert::allIsInstanceOf(
            $contactPerson,
            ContactPerson::class,
            'All md:ContactPerson elements must be an instance of ContactPerson.'
        );
        $this->ContactPerson = $contactPerson;
    }


    /**
     * Collect the value of the AdditionalMetadataLocation property.
     *
     * @return \SAML2\XML\md\AdditionalMetadataLocation[]
     */
    public function getAdditionalMetadataLocations(): array
    {
        return $this->AdditionalMetadataLocation;
    }


    /**
     * Set the value of the AdditionalMetadataLocation property.
     *
     * @param array $additionalMetadataLocation
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setAdditionalMetadataLocations(array $additionalMetadataLocation): void
    {
        Assert::allIsInstanceOf(
            $additionalMetadataLocation,
            AdditionalMetadataLocation::class,
            'All md:AdditionalMetadataLocation elements must be an instance of AdditionalMetadataLocation'
        );
        $this->AdditionalMetadataLocation = $additionalMetadataLocation;
    }


    /**
     * Create this EntityDescriptor.
     *
     * @param \DOMElement|null $parent The EntitiesDescriptor we should append this EntityDescriptor to.
     * @return \DOMElement
     * @throws \Exception
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);
        $e->setAttribute('entityID', $this->entityID);

        foreach ($this->RoleDescriptor as $n) {
            $n->toXML($e);
        }

        if ($this->AffiliationDescriptor !== null) {
            $this->AffiliationDescriptor->toXML($e);
        }

        if ($this->Organization !== null) {
            $this->Organization->toXML($e);
        }

        foreach ($this->ContactPerson as $cp) {
            $cp->toXML($e);
        }

        foreach ($this->AdditionalMetadataLocation as $n) {
            $n->toXML($e);
        }

        return $this->signElement($e);
    }
}
