<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SAML 2 SSODescriptorType.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractSSODescriptor extends AbstractRoleDescriptor
{
    /**
     * List of ArtifactResolutionService endpoints.
     *
     * @var \SAML2\XML\md\AbstractIndexedEndpointType[]
     */
    protected $artifactResolutionServiceEndpoints = [];

    /**
     * List of SingleLogoutService endpoints.
     *
     * @var \SAML2\XML\md\AbstractEndpointType[]
     */
    protected $sloServiceEndpoints = [];

    /**
     * List of ManageNameIDService endpoints.
     *
     * @var \SAML2\XML\md\AbstractEndpointType[]
     */
    protected $manageNameIDServiceEndpoints = [];

    /**
     * List of supported NameID formats.
     *
     * Array of strings.
     *
     * @var string[]
     */
    protected $nameIDFormats = [];


    /**
     * Initialize a RoleDescriptor.
     *
     * @param string[] $protocolSupportEnumeration A set of URI specifying the protocols supported.
     * @param string|null $ID The ID for this document. Defaults to null.
     * @param int|null $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SAML2\XML\md\Extensions|null $extensions An array of extensions. Defaults to an empty array.
     * @param string|null $errorURL An URI where to redirect users for support. Defaults to null.
     * @param \SAML2\XML\md\KeyDescriptor[] $keyDescriptors An array of KeyDescriptor elements.
     *   Defaults to an empty array.
     * @param \SAML2\XML\md\Organization|null $organization The organization running this entity. Defaults to null.
     * @param \SAML2\XML\md\ContactPerson[] $contacts An array of contacts for this entity.
     *   Defaults to an empty array.
     * @param \SAML2\XML\md\AbstractIndexedEndpointType[] $artifactResolutionService An array of
     *   ArtifactResolutionEndpoint. Defaults to an empty array.
     * @param \SAML2\XML\md\AbstractEndpointType[] $singleLogoutService An array of SingleLogoutEndpoint.
     *   Defaults to an empty array.
     * @param \SAML2\XML\md\AbstractEndpointType[] $manageNameIDService An array of ManageNameIDService.
     *   Defaults to an empty array.
     * @param string[] $nameIDFormat An array of supported NameID formats. Defaults to an empty array.
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
            $contacts
        );

        $this->setArtifactResolutionServices($artifactResolutionService);
        $this->setSingleLogoutServices($singleLogoutService);
        $this->setManageNameIDServices($manageNameIDService);
        $this->setNameIDFormats($nameIDFormat);
    }


    /**
     * Collect the value of the ArtifactResolutionService-property
     *
     * @return \SAML2\XML\md\AbstractIndexedEndpointType[]
     */
    public function getArtifactResolutionServices(): array
    {
        return $this->artifactResolutionServiceEndpoints;
    }


    /**
     * Set the value of the ArtifactResolutionService-property
     *
     * @param \SAML2\XML\md\AbstractIndexedEndpointType[] $artifactResolutionServices
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setArtifactResolutionServices(array $artifactResolutionServices): void
    {
        Assert::allIsInstanceOf(
            $artifactResolutionServices,
            ArtifactResolutionService::class,
            'All md:ArtifactResolutionService endpoints must be an instance of ArtifactResolutionService.'
        );
        $this->artifactResolutionServiceEndpoints = $artifactResolutionServices;
    }


    /**
     * Collect the value of the SingleLogoutService-property
     *
     * @return \SAML2\XML\md\AbstractEndpointType[]
     */
    public function getSingleLogoutServices(): array
    {
        return $this->sloServiceEndpoints;
    }


    /**
     * Set the value of the SingleLogoutService-property
     *
     * @param \SAML2\XML\md\AbstractEndpointType[] $singleLogoutServices
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setSingleLogoutServices(array $singleLogoutServices): void
    {
        Assert::allIsInstanceOf(
            $singleLogoutServices,
            SingleLogoutService::class,
            'All md:SingleLogoutService endpoints must be an instance of SingleLogoutService.'
        );
        $this->sloServiceEndpoints = $singleLogoutServices;
    }


    /**
     * Collect the value of the ManageNameIDService-property
     *
     * @return \SAML2\XML\md\AbstractEndpointType[]
     */
    public function getManageNameIDServices(): array
    {
        return $this->manageNameIDServiceEndpoints;
    }


    /**
     * Set the value of the ManageNameIDService-property
     *
     * @param \SAML2\XML\md\AbstractEndpointType[] $manageNameIDServices
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setManageNameIDServices(array $manageNameIDServices): void
    {
        Assert::allIsInstanceOf(
            $manageNameIDServices,
            ManageNameIDService::class,
            'All md:ManageNameIDService endpoints must be an instance of ManageNameIDService.'
        );
        $this->manageNameIDServiceEndpoints = $manageNameIDServices;
    }


    /**
     * Collect the value of the NameIDFormat-property
     *
     * @return string[]
     */
    public function getNameIDFormats(): array
    {
        return $this->nameIDFormats;
    }


    /**
     * Set the value of the NameIDFormat-property
     *
     * @param string[] $nameIDFormats
     */
    protected function setNameIDFormats(array $nameIDFormats): void
    {
        Assert::allStringNotEmpty($nameIDFormats, 'All NameIDFormat must be a non-empty string.');
        $this->nameIDFormats = $nameIDFormats;
    }


    /**
     * Add this SSODescriptorType to an EntityDescriptor.
     *
     * @param  \DOMElement|null $parent The EntityDescriptor we should append this SSODescriptorType to.
     * @return \DOMElement The generated SSODescriptor DOMElement.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->artifactResolutionServiceEndpoints as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->sloServiceEndpoints as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->manageNameIDServiceEndpoints as $ep) {
            $ep->toXML($e);
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:NameIDFormat', false, $this->nameIDFormats);

        return $e;
    }
}
