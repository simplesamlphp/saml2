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
     * Initialize an EntitiyDescriptor.
     *
     * @param string $entityId The entityID of the entity described by this descriptor.
     * @param string|null $id The ID for this document. Defaults to null.
     * @param int|null $validUntil Unix time of validify for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An array of extensions.
     * @param \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor[] $roleDescriptor An array of role descriptors.
     * @param \SimpleSAML\SAML2\XML\md\AffiliationDescriptor|null $affiliationDescriptor An affiliation descriptor to
     *   use instead of role descriptors.
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization The organization responsible for the SAML entity.
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contactPerson A list of contact persons for this SAML entity.
     * @param \SimpleSAML\SAML2\XML\md\AdditionalMetadataLocation[] $additionalMetadataLocation A list of
     *   additional metadata locations.
     * @param \DOMAttr[] $namespacedAttribute
     *
     * @throws \Exception
     */
    public function __construct(
        protected string $entityId,
        ?string $id = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        Extensions $extensions = null,
        protected array $roleDescriptor = [],
        protected ?AffiliationDescriptor $affiliationDescriptor = null,
        protected ?Organization $organization = null,
        protected array $contactPerson = [],
        protected array $additionalMetadataLocation = [],
        array $namespacedAttribute = []
    ) {
        Assert::false(
            (empty($roleDescriptor) && $affiliationDescriptor === null),
            'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.',
            ProtocolViolationException::class,
        );
        Assert::validURI($entityId, SchemaViolationException::class); // Covers the empty string
        Assert::maxLength(
            $entityId,
            C::ENTITYID_MAX_LENGTH,
            sprintf('The entityID attribute cannot be longer than %d characters.', C::ENTITYID_MAX_LENGTH),
            ProtocolViolationException::class
        );
        Assert::allIsInstanceOf(
            $roleDescriptor,
            AbstractRoleDescriptor::class,
            'All role descriptors must extend AbstractRoleDescriptor.'
        );
        Assert::allIsInstanceOf(
            $contactPerson,
            ContactPerson::class,
            'All md:ContactPerson elements must be an instance of ContactPerson.'
        );
        Assert::allIsInstanceOf(
            $additionalMetadataLocation,
            AdditionalMetadataLocation::class,
            'All md:AdditionalMetadataLocation elements must be an instance of AdditionalMetadataLocation'
        );

        parent::__construct($id, $validUntil, $cacheDuration, $extensions, $namespacedAttribute);
    }


    /**
     * Convert an existing XML into an EntityDescriptor object
     *
     * @param \DOMElement $xml An existing EntityDescriptor XML document.
     * @return \SimpleSAML\SAML2\XML\md\EntityDescriptor An object representing the given document.
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
    public function getEntityId(): string
    {
        return $this->entityId;
    }


    /**
     * Collect the value of the RoleDescriptor property.
     *
     * @return \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor[]
     */
    public function getRoleDescriptor(): array
    {
        return $this->roleDescriptor;
    }


    /**
     * Collect the value of the AffiliationDescriptor property.
     *
     * @return \SimpleSAML\SAML2\XML\md\AffiliationDescriptor|null
     */
    public function getAffiliationDescriptor(): ?AffiliationDescriptor
    {
        return $this->affiliationDescriptor;
    }


    /**
     * Collect the value of the Organization property.
     *
     * @return \SimpleSAML\SAML2\XML\md\Organization|null
     */
    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }


    /**
     * Collect the value of the ContactPerson property.
     *
     * @return \SimpleSAML\SAML2\XML\md\ContactPerson[]
     */
    public function getContactPerson(): array
    {
        return $this->contactPerson;
    }


    /**
     * Collect the value of the AdditionalMetadataLocation property.
     *
     * @return \SimpleSAML\SAML2\XML\md\AdditionalMetadataLocation[]
     */
    public function getAdditionalMetadataLocation(): array
    {
        return $this->additionalMetadataLocation;
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
        $e->setAttribute('entityID', $this->getEntityId());

        foreach ($this->getAttributesNS() as $attr) {
            $e->setAttributeNS($attr['namespaceURI'], $attr['qualifiedName'], $attr['value']);
        }

        foreach ($this->getRoleDescriptor() as $n) {
            $n->toXML($e);
        }

        $this->getAffiliationDescriptor()?->toXML($e);
        $this->getOrganization()?->toXML($e);

        foreach ($this->getContactPerson() as $cp) {
            $cp->toXML($e);
        }

        foreach ($this->getAdditionalMetadataLocation() as $n) {
            $n->toXML($e);
        }

        return $e;
    }
}
