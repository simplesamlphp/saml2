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
abstract class AbstractSSODescriptor extends AbstractRoleDescriptorType
{
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
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     *   The organization running this entity. Defaults to null.
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
        protected array $artifactResolutionService = [],
        protected array $singleLogoutService = [],
        protected array $manageNameIDService = [],
        protected array $nameIDFormat = [],
    ) {
        Assert::allIsInstanceOf(
            $artifactResolutionService,
            ArtifactResolutionService::class,
            'All md:ArtifactResolutionService endpoints must be an instance of ArtifactResolutionService.',
        );
        Assert::allIsInstanceOf(
            $singleLogoutService,
            SingleLogoutService::class,
            'All md:SingleLogoutService endpoints must be an instance of SingleLogoutService.',
        );
        Assert::allIsInstanceOf(
            $manageNameIDService,
            ManageNameIDService::class,
            'All md:ManageNameIDService endpoints must be an instance of ManageNameIDService.',
        );
        Assert::allIsInstanceOf($nameIDFormat, NameIDFormat::class, ProtocolViolationException::class);

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
        );
    }


    /**
     * Collect the value of the ArtifactResolutionService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType[]
     */
    public function getArtifactResolutionService(): array
    {
        return $this->artifactResolutionService;
    }


    /**
     * Collect the value of the SingleLogoutService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    public function getSingleLogoutService(): array
    {
        return $this->singleLogoutService;
    }


    /**
     * Collect the value of the ManageNameIDService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    public function getManageNameIDService(): array
    {
        return $this->manageNameIDService;
    }


    /**
     * Collect the value of the NameIDFormat-property
     *
     * @return \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    public function getNameIDFormat(): array
    {
        return $this->nameIDFormat;
    }


    /**
     * Add this SSODescriptorType to an EntityDescriptor.
     *
     * @param  \DOMElement|null $parent The EntityDescriptor we should append this SSODescriptorType to.
     * @return \DOMElement The generated SSODescriptor DOMElement.
     */
    public function toUnsignedXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        foreach ($this->getArtifactResolutionService() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getSingleLogoutService() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getManageNameIDService() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getNameIDFormat() as $nidFormat) {
            $nidFormat->toXML($e);
        }

        return $e;
    }
}
