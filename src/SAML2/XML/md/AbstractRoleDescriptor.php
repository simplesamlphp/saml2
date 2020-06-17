<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use InvalidArgumentException;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SAML 2 RoleDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractRoleDescriptor extends AbstractMetadataDocument
{
    /**
     * List of supported protocols.
     *
     * @var string[]
     */
    protected $protocolSupportEnumeration = [];

    /**
     * Error URL for this role.
     *
     * @var string|null
     */
    protected $errorURL = null;

    /**
     * KeyDescriptor elements.
     *
     * Array of \SAML2\XML\md\KeyDescriptor elements.
     *
     * @var \SAML2\XML\md\KeyDescriptor[]
     */
    protected $KeyDescriptors = [];

    /**
     * Organization of this role.
     *
     * @var \SAML2\XML\md\Organization|null
     */
    protected $Organization = null;

    /**
     * ContactPerson elements for this role.
     *
     * Array of \SAML2\XML\md\ContactPerson objects.
     *
     * @var \SAML2\XML\md\ContactPerson[]
     */
    protected $ContactPersons = [];


    /**
     * Initialize a RoleDescriptor.
     *
     * @param string[] $protocolSupportEnumeration A set of URI specifying the protocols supported.
     * @param string|null $ID The ID for this document. Defaults to null.
     * @param int|null $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SAML2\XML\md\Extensions|null $extensions An Extensions object. Defaults to null.
     * @param string|null $errorURL An URI where to redirect users for support. Defaults to null.
     * @param \SAML2\XML\md\KeyDescriptor[] $keyDescriptors An array of KeyDescriptor elements. Defaults to an empty array.
     * @param \SAML2\XML\md\Organization|null $organization The organization running this entity. Defaults to null.
     * @param \SAML2\XML\md\ContactPerson[] $contacts An array of contacts for this entity. Defaults to an empty array.
     */
    public function __construct(
        array $protocolSupportEnumeration,
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        array $keyDescriptors = [],
        ?Organization $organization = null,
        array $contacts = []
    ) {
        parent::__construct($ID, $validUntil, $cacheDuration, $extensions);

        $this->setProtocolSupportEnumeration($protocolSupportEnumeration);
        $this->setErrorURL($errorURL);
        $this->setKeyDescriptors($keyDescriptors);
        $this->setOrganization($organization);
        $this->setContactPersons($contacts);
    }


    /**
     * Collect the value of the errorURL property.
     *
     * @return string|null
     */
    public function getErrorURL()
    {
        return $this->errorURL;
    }


    /**
     * Set the value of the errorURL property.
     *
     * @param string|null $errorURL
     * @throws \InvalidArgumentException
     */
    protected function setErrorURL(?string $errorURL = null): void
    {
        if (!is_null($errorURL) && !filter_var($errorURL, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('RoleDescriptor errorURL is not a valid URL.');
        }
        $this->errorURL = $errorURL;
    }


    /**
     * Collect the value of the protocolSupportEnumeration property.
     *
     * @return string[]
     */
    public function getProtocolSupportEnumeration()
    {
        return $this->protocolSupportEnumeration;
    }


    /**
     * Set the value of the ProtocolSupportEnumeration property.
     *
     * @param string[] $protocols
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException if the qualified name of the supplied element is wrong
     */
    protected function setProtocolSupportEnumeration(array $protocols): void
    {
        Assert::minCount($protocols, 1, 'At least one protocol must be supported by this ' . static::class . '.');
        Assert::allStringNotEmpty($protocols, 'Cannot specify an empty string as a supported protocol.');
        $this->protocolSupportEnumeration = $protocols;
    }


    /**
     * Collect the value of the Organization property.
     *
     * @return \SAML2\XML\md\Organization|null
     */
    public function getOrganization()
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
     * Collect the value of the ContactPersons property.
     *
     * @return \SAML2\XML\md\ContactPerson[]
     */
    public function getContactPersons()
    {
        return $this->ContactPersons;
    }


    /**
     * Set the value of the ContactPerson property.
     *
     * @param \SAML2\XML\md\ContactPerson[] $contactPersons
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setContactPersons(array $contactPersons): void
    {
        Assert::allIsInstanceOf(
            $contactPersons,
            ContactPerson::class,
            'All contacts must be an instance of md:ContactPerson'
        );
        $this->ContactPersons = $contactPersons;
    }


    /**
     * Collect the value of the KeyDescriptors property.
     *
     * @return \SAML2\XML\md\KeyDescriptor[]
     */
    public function getKeyDescriptors()
    {
        return $this->KeyDescriptors;
    }


    /**
     * Set the value of the KeyDescriptor property.
     *
     * @param \SAML2\XML\md\KeyDescriptor[] $keyDescriptor
     */
    protected function setKeyDescriptors(array $keyDescriptor): void
    {
        Assert::allIsInstanceOf(
            $keyDescriptor,
            KeyDescriptor::class,
            'All key descriptors must be an instance of md:KeyDescriptor'
        );
        $this->KeyDescriptors = $keyDescriptor;
    }


    /**
     * Add this RoleDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this endpoint to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        $e->setAttribute('protocolSupportEnumeration', implode(' ', $this->protocolSupportEnumeration));

        if ($this->errorURL !== null) {
            $e->setAttribute('errorURL', $this->errorURL);
        }

        foreach ($this->KeyDescriptors as $kd) {
            $kd->toXML($e);
        }

        if ($this->Organization !== null) {
            $this->Organization->toXML($e);
        }

        foreach ($this->ContactPersons as $cp) {
            $cp->toXML($e);
        }

        return $e;
    }
}
