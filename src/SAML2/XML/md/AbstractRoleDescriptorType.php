<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\XsNamespace as NS;

use function implode;

/**
 * Class representing SAML2 RoleDescriptorType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractRoleDescriptorType extends AbstractMetadataDocument
{
    use ExtendableAttributesTrait;

    /** The namespace-attribute for the xs:anyAttribute element */
    public const XS_ANY_ATTR_NAMESPACE = NS::OTHER;


    /**
     * Initialize a RoleDescriptor.
     *
     * @param string[] $protocolSupportEnumeration A set of URI specifying the protocols supported.
     * @param string|null $ID The ID for this document. Defaults to null.
     * @param \DateTimeImmutable|null $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An Extensions object. Defaults to null.
     * @param string|null $errorURL An URI where to redirect users for support. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptor An array of KeyDescriptor elements.
     *   Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     *   The organization running this entity. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contact
     *   An array of contacts for this entity. Defaults to an empty array.
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    public function __construct(
        protected array $protocolSupportEnumeration,
        ?string $ID = null,
        ?DateTimeImmutable $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        protected ?string $errorURL = null,
        protected array $keyDescriptor = [],
        protected ?Organization $organization = null,
        protected array $contact = [],
        array $namespacedAttributes = [],
    ) {
        Assert::maxCount($protocolSupportEnumeration, C::UNBOUNDED_LIMIT);
        Assert::minCount(
            $protocolSupportEnumeration,
            1,
            'At least one protocol must be supported by this ' . static::NS_PREFIX . ':' . static::getLocalName() . '.',
        );
        Assert::allValidURI($protocolSupportEnumeration, SchemaViolationException::class);
        Assert::nullOrValidURI($errorURL, SchemaViolationException::class); // Covers the empty string
        Assert::maxCount($contact, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf(
            $contact,
            ContactPerson::class,
            'All contacts must be an instance of md:ContactPerson',
        );
        Assert::maxCount($keyDescriptor, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf(
            $keyDescriptor,
            KeyDescriptor::class,
            'All key descriptors must be an instance of md:KeyDescriptor',
        );

        parent::__construct($ID, $validUntil, $cacheDuration, $extensions);

        $this->setAttributesNS($namespacedAttributes);
    }


    /**
     * Collect the value of the errorURL property.
     *
     * @return string|null
     */
    public function getErrorURL(): ?string
    {
        return $this->errorURL;
    }


    /**
     * Collect the value of the protocolSupportEnumeration property.
     *
     * @return string[]
     */
    public function getProtocolSupportEnumeration(): array
    {
        return $this->protocolSupportEnumeration;
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
     * Collect the value of the ContactPersons property.
     *
     * @return \SimpleSAML\SAML2\XML\md\ContactPerson[]
     */
    public function getContactPerson(): array
    {
        return $this->contact;
    }


    /**
     * Collect the value of the KeyDescriptors property.
     *
     * @return \SimpleSAML\SAML2\XML\md\KeyDescriptor[]
     */
    public function getKeyDescriptor(): array
    {
        return $this->keyDescriptor;
    }


    /**
     * Add this RoleDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this endpoint to.
     * @return \DOMElement
     */
    public function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);
        $e->setAttribute('protocolSupportEnumeration', implode(' ', $this->getProtocolSupportEnumeration()));

        if ($this->getErrorURL() !== null) {
            $e->setAttribute('errorURL', $this->getErrorURL());
        }

        foreach ($this->getKeyDescriptor() as $kd) {
            $kd->toXML($e);
        }

        $this->getOrganization()?->toXML($e);

        foreach ($this->getContactPerson() as $cp) {
            $cp->toXML($e);
        }

        foreach ($this->getAttributesNS() as $attr) {
            $attr->toXML($e);
        }

        return $e;
    }
}
