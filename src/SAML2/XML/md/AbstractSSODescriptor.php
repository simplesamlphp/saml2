<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Utils as XMLUtils;

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
     * @var \SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType[]
     */
    protected array $artifactResolutionServiceEndpoints = [];

    /**
     * List of SingleLogoutService endpoints.
     *
     * @var \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    protected array $sloServiceEndpoints = [];

    /**
     * List of ManageNameIDService endpoints.
     *
     * @var \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    protected array $manageNameIDServiceEndpoints = [];

    /**
     * List of supported NameID formats.
     *
     * Array of strings.
     *
     * @var \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    protected array $nameIDFormats = [];


    /**
     * Initialize a RoleDescriptor.
     *
     * @param string[] $protocolSupportEnumeration A set of URI specifying the protocols supported.
     * @param string|null $ID The ID for this document. Defaults to null.
     * @param int|null $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An array of extensions. Defaults to an empty array.
     * @param string|null $errorURL An URI where to redirect users for support. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptors An array of KeyDescriptor elements.
     *   Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization The organization running this entity. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contacts An array of contacts for this entity.
     *   Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType[] $artifactResolutionService An array of
     *   ArtifactResolutionEndpoint. Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\AbstractEndpointType[] $singleLogoutService An array of SingleLogoutEndpoint.
     *   Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\AbstractEndpointType[] $manageNameIDService An array of ManageNameIDService.
     *   Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\NameIDFormat[] $nameIDFormat An array of supported NameID formats.
     *   Defaults to an empty array.
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
     * @return \SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType[]
     */
    public function getArtifactResolutionServices(): array
    {
        return $this->artifactResolutionServiceEndpoints;
    }


    /**
     * Set the value of the ArtifactResolutionService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType[] $artifactResolutionServices
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
     * @return \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    public function getSingleLogoutServices(): array
    {
        return $this->sloServiceEndpoints;
    }


    /**
     * Set the value of the SingleLogoutService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AbstractEndpointType[] $singleLogoutServices
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
     * @return \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    public function getManageNameIDServices(): array
    {
        return $this->manageNameIDServiceEndpoints;
    }


    /**
     * Set the value of the ManageNameIDService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AbstractEndpointType[] $manageNameIDServices
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
     * @return \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    public function getNameIDFormats(): array
    {
        return $this->nameIDFormats;
    }


    /**
     * Set the value of the NameIDFormat-property
     *
     * @param \SimpleSAML\SAML2\XML\md\NameIDFormat[] $nameIDFormats
     */
    protected function setNameIDFormats(array $nameIDFormats): void
    {
        Assert::allIsInstanceOf($nameIDFormats, NameIDFormat::class, ProtocolViolationException::class);
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

        foreach ($this->nameIDFormats as $nidFormat) {
            $nidFormat->toXML($e);
        }

        return $e;
    }
}
