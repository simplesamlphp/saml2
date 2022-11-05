<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function preg_split;

/**
 * Class representing SAML 2 IDPSSODescriptor.
 *
 * @package simplesamlphp/saml2
 */
final class IDPSSODescriptor extends AbstractSSODescriptor
{
    /**
     * Whether AuthnRequests sent to this IdP should be signed.
     *
     * @var bool|null
     */
    protected ?bool $wantAuthnRequestsSigned = null;

    /**
     * List of SingleSignOnService endpoints.
     *
     * @var \SimpleSAML\SAML2\XML\md\SingleSignOnService[]
     */
    protected array $ssoServiceEndpoints = [];

    /**
     * List of NameIDMappingService endpoints.
     *
     * @var \SimpleSAML\SAML2\XML\md\NameIDMappingService[]
     */
    protected array $nameIDMappingServiceEndpoints = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * @var \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[]
     */
    protected array $assertionIDRequestServiceEndpoints = [];

    /**
     * List of supported attribute profiles.
     *
     * @var \SimpleSAML\SAML2\XML\md\AttributeProfile[]
     */
    protected array $attributeProfiles = [];

    /**
     * List of supported attributes.
     *
     * @var \SimpleSAML\SAML2\XML\saml\Attribute[]
     */
    protected array $attributes = [];


    /**
     * IDPSSODescriptor constructor.
     *
     * @param \SimpleSAML\SAML2\XML\md\SingleSignOnService[] $ssoServiceEndpoints
     * @param string[] $protocolSupportEnumeration
     * @param bool|null $wantAuthnRequestsSigned
     * @param \SimpleSAML\SAML2\XML\md\NameIDMappingService[] $nameIDMappingServiceEndpoints
     * @param \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[] $assertionIDRequestServiceEndpoints
     * @param \SimpleSAML\SAML2\XML\md\AttributeProfile[] $attributeProfiles
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[] $attributes
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptors
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contacts
     * @param \SimpleSAML\SAML2\XML\md\ArtifactResolutionService[] $artifactResolutionService
     * @param \SimpleSAML\SAML2\XML\md\SingleLogoutService[] $singleLogoutService
     * @param \SimpleSAML\SAML2\XML\md\ManageNameIDService[] $manageNameIDService
     * @param \SimpleSAML\SAML2\XML\md\NameIDFormat[] $nameIDFormat
     */
    public function __construct(
        array $ssoServiceEndpoints,
        array $protocolSupportEnumeration,
        ?bool $wantAuthnRequestsSigned = null,
        array $nameIDMappingServiceEndpoints = [],
        array $assertionIDRequestServiceEndpoints = [],
        array $attributeProfiles = [],
        array $attributes = [],
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        array $keyDescriptors = [],
        ?Organization $organization = null,
        array $contacts = [],
        array $artifactResolutionService = [],
        array $singleLogoutService = [],
        array $manageNameIDService = [],
        array $nameIDFormat = []
    ) {
        parent::__construct(
            $protocolSupportEnumeration,
            $ID,
            $validUntil,
            $cacheDuration,
            $extensions,
            $errorURL,
            $keyDescriptors,
            $organization,
            $contacts,
            $artifactResolutionService,
            $singleLogoutService,
            $manageNameIDService,
            $nameIDFormat
        );
        $this->setSingleSignOnServices($ssoServiceEndpoints);
        $this->setWantAuthnRequestsSigned($wantAuthnRequestsSigned);
        $this->setNameIDMappingServices($nameIDMappingServiceEndpoints);
        $this->setAssertionIDRequestService($assertionIDRequestServiceEndpoints);
        $this->setAttributeProfiles($attributeProfiles);
        $this->setSupportedAttributes($attributes);
    }


    /**
     * Collect the value of the WantAuthnRequestsSigned-property
     *
     * @return bool|null
     */
    public function wantAuthnRequestsSigned(): ?bool
    {
        return $this->wantAuthnRequestsSigned;
    }


    /**
     * Set the value of the WantAuthnRequestsSigned-property
     *
     * @param bool|null $flag
     */
    protected function setWantAuthnRequestsSigned(?bool $flag = null): void
    {
        $this->wantAuthnRequestsSigned = $flag;
    }


    /**
     * Get the SingleSignOnService endpoints
     *
     * @return \SimpleSAML\SAML2\XML\md\SingleSignOnService[]
     */
    public function getSingleSignOnServices(): array
    {
        return $this->ssoServiceEndpoints;
    }


    /**
     * Set the SingleSignOnService endpoints
     *
     * @param \SimpleSAML\SAML2\XML\md\SingleSignOnService[] $singleSignOnServices
     */
    protected function setSingleSignOnServices(array $singleSignOnServices): void
    {
        Assert::minCount($singleSignOnServices, 1, 'At least one SingleSignOnService must be specified.');
        Assert::allIsInstanceOf(
            $singleSignOnServices,
            SingleSignOnService::class,
            'All md:SingleSignOnService endpoints must be an instance of SingleSignOnService.'
        );
        $this->ssoServiceEndpoints = $singleSignOnServices;
    }


    /**
     * Get the NameIDMappingService endpoints
     *
     * @return \SimpleSAML\SAML2\XML\md\NameIDMappingService[]
     */
    public function getNameIDMappingServices(): array
    {
        return $this->nameIDMappingServiceEndpoints;
    }


    /**
     * Set the NameIDMappingService endpoints
     *
     * @param \SimpleSAML\SAML2\XML\md\NameIDMappingService[] $nameIDMappingServices
     */
    protected function setNameIDMappingServices(array $nameIDMappingServices): void
    {
        Assert::allIsInstanceOf(
            $nameIDMappingServices,
            NameIDMappingService::class,
            'All md:NameIDMappingService endpoints must be an instance of NameIDMappingService.'
        );
        $this->nameIDMappingServiceEndpoints = $nameIDMappingServices;
    }


    /**
     * Collect the AssertionIDRequestService endpoints
     *
     * @return \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[]
     */
    public function getAssertionIDRequestServices(): array
    {
        return $this->assertionIDRequestServiceEndpoints;
    }


    /**
     * Set the AssertionIDRequestService endpoints
     *
     * @param \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[] $assertionIDRequestServices
     */
    protected function setAssertionIDRequestService(array $assertionIDRequestServices): void
    {
        Assert::allIsInstanceOf(
            $assertionIDRequestServices,
            AssertionIDRequestService::class,
            'All md:AssertionIDRequestService endpoints must be an instance of AssertionIDRequestService.'
        );
        $this->assertionIDRequestServiceEndpoints = $assertionIDRequestServices;
    }


    /**
     * Get the attribute profiles supported
     *
     * @return \SimpleSAML\SAML2\XML\md\AttributeProfile[]
     */
    public function getAttributeProfiles(): array
    {
        return $this->attributeProfiles;
    }


    /**
     * Set the attribute profiles supported
     *
     * @param \SimpleSAML\SAML2\XML\md\AttributeProfile[] $attributeProfiles
     */
    protected function setAttributeProfiles(array $attributeProfiles): void
    {
        Assert::allIsInstanceOf($attributeProfiles, AttributeProfile::class);
        $this->attributeProfiles = $attributeProfiles;
    }


    /**
     * Get the attributes supported by this IdP
     *
     * @return \SimpleSAML\SAML2\XML\saml\Attribute[]
     */
    public function getSupportedAttributes(): array
    {
        return $this->attributes;
    }


    /**
     * Set the attributes supported by this IdP
     *
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[] $attributes
     */
    protected function setSupportedAttributes(array $attributes): void
    {
        Assert::allIsInstanceOf(
            $attributes,
            Attribute::class,
            'All md:Attribute elements must be an instance of Attribute.'
        );
        $this->attributes = $attributes;
    }


    /**
     * Initialize an IDPSSODescriptor.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return \SimpleSAML\SAML2\XML\md\IDPSSODescriptor
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'IDPSSODescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, IDPSSODescriptor::NS, InvalidDOMElementException::class);

        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');
        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount(
            $orgs,
            1,
            'More than one Organization found in this descriptor',
            TooManyElementsException::class
        );

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one md:Extensions element is allowed.',
            TooManyElementsException::class
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount(
            $signature,
            1,
            'Only one ds:Signature element is allowed.',
            TooManyElementsException::class
        );

        $idpssod = new static(
            SingleSignOnService::getChildrenOfClass($xml),
            preg_split('/[\s]+/', trim($protocols)),
            self::getBooleanAttribute($xml, 'WantAuthnRequestsSigned', null),
            NameIDMappingService::getChildrenOfClass($xml),
            AssertionIDRequestService::getChildrenOfClass($xml),
            AttributeProfile::getChildrenOfClass($xml),
            Attribute::getChildrenOfClass($xml),
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? XMLUtils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getAttribute($xml, 'errorURL', null),
            KeyDescriptor::getChildrenOfClass($xml),
            !empty($orgs) ? $orgs[0] : null,
            ContactPerson::getChildrenOfClass($xml),
            ArtifactResolutionService::getChildrenOfClass($xml),
            SingleLogoutService::getChildrenOfClass($xml),
            ManageNameIDService::getChildrenOfClass($xml),
            NameIDFormat::getChildrenOfClass($xml)
        );

        if (!empty($signature)) {
            $idpssod->setSignature($signature[0]);
            $idpssod->setXML($xml);
        }
        return $idpssod;
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

        if (is_bool($this->wantAuthnRequestsSigned)) {
            $e->setAttribute('WantAuthnRequestsSigned', $this->wantAuthnRequestsSigned ? 'true' : 'false');
        }

        foreach ($this->getSingleSignOnServices() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getNameIDMappingServices() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getAssertionIDRequestServices() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getAttributeProfiles() as $ap) {
            $ap->toXML($e);
        }

        foreach ($this->getSupportedAttributes() as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
