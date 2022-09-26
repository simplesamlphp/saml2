<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function is_null;

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
    protected string $entityID;

    /**
     * Array with all roles for this entity.
     *
     * Array of \SimpleSAML\SAML2\XML\md\RoleDescriptor objects (and subclasses of RoleDescriptor).
     *
     * @var \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor[]
     */
    protected array $RoleDescriptor = [];

    /**
     * AffiliationDescriptor of this entity.
     *
     * @var \SimpleSAML\SAML2\XML\md\AffiliationDescriptor|null
     */
    protected ?AffiliationDescriptor $AffiliationDescriptor = null;

    /**
     * Organization of this entity.
     *
     * @var \SimpleSAML\SAML2\XML\md\Organization|null
     */
    protected ?Organization $Organization = null;

    /**
     * ContactPerson elements for this entity.
     *
     * @var \SimpleSAML\SAML2\XML\md\ContactPerson[]
     */
    protected array $ContactPerson = [];

    /**
     * AdditionalMetadataLocation elements for this entity.
     *
     * @var \SimpleSAML\SAML2\XML\md\AdditionalMetadataLocation[]
     */
    protected array $AdditionalMetadataLocation = [];


    /**
     * Initialize an EntitiyDescriptor.
     *
     * @param string $entityID The entityID of the entity described by this descriptor.
     * @param string|null $id The ID for this document. Defaults to null.
     * @param int|null $validUntil Unix time of validify for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An array of extensions.
     * @param \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor[] $roleDescriptors An array of role descriptors.
     * @param \SimpleSAML\SAML2\XML\md\AffiliationDescriptor|null $affiliationDescriptor An affiliation descriptor to
     *   use instead of role descriptors.
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization The organization responsible for the SAML entity.
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contacts A list of contact persons for this SAML entity.
     * @param \SimpleSAML\SAML2\XML\md\AdditionalMetadataLocation[] $additionalMdLocations A list of
     *   additional metadata locations.
     * @param \DOMAttr[] $namespacedAttributes
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
        array $additionalMdLocations = [],
        array $namespacedAttributes = []
    ) {
        Assert::false(
            (empty($roleDescriptors) && $affiliationDescriptor === null),
            'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.',
            ProtocolViolationException::class,
        );

        parent::__construct($id, $validUntil, $cacheDuration, $extensions, $namespacedAttributes);

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
     * @return \SimpleSAML\SAML2\XML\md\EntityDescriptor An object representing the given document.
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
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
                || ($node->namespaceURI !== C::NS_MD)
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

        Assert::false(
            empty($roleDescriptors) && is_null($affiliationDescriptor),
            'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.',
            ProtocolViolationException::class,
        );
        Assert::false(
            !empty($roleDescriptors) && !is_null($affiliationDescriptor),
            'AffiliationDescriptor cannot be combined with other RoleDescriptor elements in EntityDescriptor.',
            ProtocolViolationException::class,
        );

        $entity = new static(
            $entityID,
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? XMLUtils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            $roleDescriptors,
            $affiliationDescriptor,
            $organization,
            $contactPersons,
            $additionalMetadataLocation,
            self::getAttributesNSFromXML($xml)
        );

        if (!empty($signature)) {
            $entity->setSignature($signature[0]);
            $entity->setXML($xml);
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
        return $this->entityID;
    }


    /**
     * Set the value of the entityID-property
     * @param string $entityId
     */
    protected function setEntityID(string $entityId): void
    {
        Assert::validURI($entityId, SchemaViolationException::class); // Covers the empty string
        Assert::maxLength(
            $entityId,
            C::ENTITYID_MAX_LENGTH,
            sprintf('The entityID attribute cannot be longer than %d characters.', C::ENTITYID_MAX_LENGTH),
            ProtocolViolationException::class
        );
        $this->entityID = $entityId;
    }


    /**
     * Collect the value of the RoleDescriptor property.
     *
     * @return \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor[]
     */
    public function getRoleDescriptors(): array
    {
        return $this->RoleDescriptor;
    }


    /**
     * Set the value of the RoleDescriptor property.
     *
     * @param \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor[] $roleDescriptors
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
     * @return \SimpleSAML\SAML2\XML\md\AffiliationDescriptor|null
     */
    public function getAffiliationDescriptor(): ?AffiliationDescriptor
    {
        return $this->AffiliationDescriptor;
    }


    /**
     * Set the value of the AffliationDescriptor property.
     *
     * @param \SimpleSAML\SAML2\XML\md\AffiliationDescriptor|null $affiliationDescriptor
     */
    protected function setAffiliationDescriptor(?AffiliationDescriptor $affiliationDescriptor = null): void
    {
        $this->AffiliationDescriptor = $affiliationDescriptor;
    }


    /**
     * Collect the value of the Organization property.
     *
     * @return \SimpleSAML\SAML2\XML\md\Organization|null
     */
    public function getOrganization(): ?Organization
    {
        return $this->Organization;
    }


    /**
     * Set the value of the Organization property.
     *
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     */
    protected function setOrganization(?Organization $organization = null): void
    {
        $this->Organization = $organization;
    }


    /**
     * Collect the value of the ContactPerson property.
     *
     * @return \SimpleSAML\SAML2\XML\md\ContactPerson[]
     */
    public function getContactPersons(): array
    {
        return $this->ContactPerson;
    }


    /**
     * Set the value of the ContactPerson property.
     *
     * @param array $contactPerson
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
     * @return \SimpleSAML\SAML2\XML\md\AdditionalMetadataLocation[]
     */
    public function getAdditionalMetadataLocations(): array
    {
        return $this->AdditionalMetadataLocation;
    }


    /**
     * Set the value of the AdditionalMetadataLocation property.
     *
     * @param array $additionalMetadataLocation
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

        foreach ($this->getAttributesNS() as $attr) {
            $e->setAttributeNS($attr['namespaceURI'], $attr['qualifiedName'], $attr['value']);
        }

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

        if ($this->signer !== null) {
            $signedXML = $this->doSign($e);
            $signedXML->insertBefore($this->signature->toXML($signedXML), $signedXML->firstChild);
            return $signedXML;
        }

        return $e;
    }
}
