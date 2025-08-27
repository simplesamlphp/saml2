<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\{AnyURIListValue, SAMLAnyURIValue, SAMLDateTimeValue};
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XMLSchema\Type\{DurationValue, IDValue};
use SimpleSAML\XMLSchema\XML\Enumeration\NamespaceEnum;

use function strval;

/**
 * Class representing SAML2 RoleDescriptorType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractRoleDescriptorType extends AbstractMetadataDocument
{
    use ExtendableAttributesTrait;


    /** The namespace-attribute for the xs:anyAttribute element */
    public const XS_ANY_ATTR_NAMESPACE = NamespaceEnum::Other;


    /**
     * Initialize a RoleDescriptor.
     *
     * @param \SimpleSAML\SAML2\Type\AnyURIListValue $protocolSupportEnumeration
     *   A set of URI specifying the protocols supported.
     * @param \SimpleSAML\XMLSchema\Type\IDValue|null $ID The ID for this document. Defaults to null.
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $validUntil Unix time of validity for this document.
     *   Defaults to null.
     * @param \SimpleSAML\XMLSchema\Type\DurationValue|null $cacheDuration Maximum time this document can be cached.
     *   Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An Extensions object. Defaults to null.
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $errorURL An URI where to redirect users for support.
     *   Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptor An array of KeyDescriptor elements.
     *   Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     *   The organization running this entity. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contact
     *   An array of contacts for this entity. Defaults to an empty array.
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    public function __construct(
        protected AnyURIListValue $protocolSupportEnumeration,
        ?IDValue $ID = null,
        ?SAMLDateTimeValue $validUntil = null,
        ?DurationValue $cacheDuration = null,
        ?Extensions $extensions = null,
        protected ?SAMLAnyURIValue $errorURL = null,
        protected array $keyDescriptor = [],
        protected ?Organization $organization = null,
        protected array $contact = [],
        array $namespacedAttributes = [],
    ) {
        /**
         * A whitespace-delimited set of URIs that identify the set of protocol specifications supported by the
         * role element. For SAML V2.0 entities, this set MUST include the SAML protocol namespace URI,
         * urn:oasis:names:tc:SAML:2.0:protocol.
         */
        Assert::contains(
            strval($protocolSupportEnumeration),
            C::NS_SAMLP,
            'SAML v2.0 entities MUST include the SAML protocol namespace URI in their'
            . ' protocolSupportEnumeration attribute',
            ProtocolViolationException::class,
        );
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
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null
     */
    public function getErrorURL(): ?SAMLAnyURIValue
    {
        return $this->errorURL;
    }


    /**
     * Collect the value of the protocolSupportEnumeration property.
     *
     * @return \SimpleSAML\SAML2\Type\AnyURIListValue
     */
    public function getProtocolSupportEnumeration(): AnyURIListValue
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
        $e->setAttribute('protocolSupportEnumeration', strval($this->getProtocolSupportEnumeration()));

        if ($this->getErrorURL() !== null) {
            $e->setAttribute('errorURL', strval($this->getErrorURL()));
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
