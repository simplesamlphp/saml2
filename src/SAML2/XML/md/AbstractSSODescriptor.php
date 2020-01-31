<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;

/**
 * Class representing SAML 2 SSODescriptorType.
 *
 * @package SimpleSAMLphp
 */
abstract class AbstractSSODescriptor extends AbstractRoleDescriptor
{
    /**
     * List of ArtifactResolutionService endpoints.
     *
     * Array with IndexedEndpointType objects.
     *
     * @var \SAML2\XML\md\AbstractIndexedEndpointType[]
     */
    private $ArtifactResolutionService = [];

    /**
     * List of SingleLogoutService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SAML2\XML\md\AbstractEndpointType[]
     */
    private $SingleLogoutService = [];

    /**
     * List of ManageNameIDService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SAML2\XML\md\AbstractEndpointType[]
     */
    private $ManageNameIDService = [];

    /**
     * List of supported NameID formats.
     *
     * Array of strings.
     *
     * @var string[]
     */
    private $NameIDFormat = [];


    /**
     * Initialize a RoleDescriptor.
     *
     * @param string[]                           $protocolSupportEnumeration A set of URI specifying the protocols supported.
     * @param string|null                        $ID The ID for this document. Defaults to null.
     * @param int|null                           $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null                        $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param Extensions[]|null                  $extensions An array of extensions. Defaults to an empty array.
     * @param string|null                        $errorURL An URI where to redirect users for support. Defaults to null.
     * @param KeyDescriptor[]|null               $keyDescriptors An array of KeyDescriptor elements. Defaults to an empty array.
     * @param Organization|null                  $organization The organization running this entity. Defaults to null.
     * @param ContactPerson[]|null               $contacts An array of contacts for this entity. Defaults to an empty array.
     * @param AbstractIndexedEndpointType[]|null $artifactResolutionService An array of ArtifactResolutionEndpoint. Defaults
     * to an empty array.
     * @param AbstractEndpointType[]|null        $singleLogoutService An array of SingleLogoutEndpoint. Defaults to an empty array.
     * @param AbstractEndpointType[]|null        $manageNameIDService An array of ManageNameIDService. Defaults to an empty array.
     * @param string[]|null                      $nameIDFormat An array of supported NameID formats. Defaults to an empty array.
     */
    public function __construct(
        array $protocolSupportEnumeration,
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?array $extensions = [],
        ?string $errorURL = null,
        ?array $keyDescriptors = [],
        ?Organization $organization = null,
        ?array $contacts = [],
        ?array $artifactResolutionService = [],
        ?array $singleLogoutService = [],
        ?array $manageNameIDService = [],
        ?array $nameIDFormat = []
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

        $this->setArtifactResolutionService($artifactResolutionService);
        $this->setSingleLogoutService($singleLogoutService);
        $this->setManageNameIDService($manageNameIDService);
        $this->setNameIDFormat($nameIDFormat);
    }


    /**
     * Process an XML element and get ArtifactResolutionService elements from it, if any.
     *
     * @param DOMElement $xml An element that may contain ArtifactResolutionService elements.
     *
     * @return array
     * @throws \Exception
     */
    public static function getArtifactResolutionServiceFromXML(DOMElement $xml): array
    {
        $ars = [];
        /** @var \DOMElement $ep */
        foreach (Utils::xpQuery($xml, './saml_metadata:ArtifactResolutionService') as $ep) {
            $ars[] = ArtifactResolutionService::fromXML($ep);
        }
        return $ars;
    }


    /**
     * Collect the value of the ArtifactResolutionService-property
     *
     * @return \SAML2\XML\md\AbstractIndexedEndpointType[]
     */
    public function getArtifactResolutionService(): array
    {
        return $this->ArtifactResolutionService;
    }


    /**
     * Set the value of the ArtifactResolutionService-property
     *
     * @param \SAML2\XML\md\AbstractIndexedEndpointType[] $artifactResolutionService
     *
     * @return void
     */
    protected function setArtifactResolutionService(array $artifactResolutionService): void
    {
        $this->ArtifactResolutionService = $artifactResolutionService;
    }


    /**
     * Add the value to the ArtifactResolutionService-property
     *
     * @param \SAML2\XML\md\AbstractIndexedEndpointType $artifactResolutionService
     *
     * @return void
     */
    public function addArtifactResolutionService(AbstractIndexedEndpointType $artifactResolutionService): void
    {
        $this->ArtifactResolutionService[] = $artifactResolutionService;
    }


    /**
     * Process an XML element and get SingleLogoutService elements from it, if any.
     *
     * @param DOMElement $xml An element that may contain ArtifactResolutionService elements.
     *
     * @return array
     * @throws \Exception
     */
    public static function getSingleLogoutServiceFromXML(DOMElement $xml): array
    {
        $slo = [];
        /** @var \DOMElement $ep */
        foreach (Utils::xpQuery($xml, './saml_metadata:SingleLogoutService') as $ep) {
            $slo[] = new AbstractEndpointType($ep);
        }
        return $slo;
    }


    /**
     * Collect the value of the SingleLogoutService-property
     *
     * @return \SAML2\XML\md\AbstractEndpointType[]
     */
    public function getSingleLogoutService(): array
    {
        return $this->SingleLogoutService;
    }


    /**
     * Set the value of the SingleLogoutService-property
     *
     * @param \SAML2\XML\md\AbstractEndpointType[] $singleLogoutService
     *
     * @return void
     */
    protected function setSingleLogoutService(array $singleLogoutService): void
    {
        $this->SingleLogoutService = $singleLogoutService;
    }


    /**
     * Add the value to the SingleLogoutService-property
     *
     * @param \SAML2\XML\md\AbstractEndpointType $singleLogoutService
     *
     * @return void
     */
    public function addSingleLogoutService(AbstractEndpointType $singleLogoutService): void
    {
        $this->SingleLogoutService[] = $singleLogoutService;
    }


    /**
     * Process an XML element and get ManageNameIDService elements from it, if any.
     *
     * @param DOMElement $xml An element that may contain ManageNameIDService elements.
     *
     * @return array
     * @throws \Exception
     */
    public static function getManageNameIDServiceFromXML(DOMElement $xml): array
    {
        $mnids = [];
        /** @var \DOMElement $ep */
        foreach (Utils::xpQuery($xml, './saml_metadata:ManageNameIDService') as $ep) {
            $mnids[] = new AbstractEndpointType($ep);
        }
        return $mnids;
    }


    /**
     * Collect the value of the ManageNameIDService-property
     *
     * @return \SAML2\XML\md\AbstractEndpointType[]
     */
    public function getManageNameIDService(): array
    {
        return $this->ManageNameIDService;
    }


    /**
     * Set the value of the ManageNameIDService-property
     *
     * @param \SAML2\XML\md\AbstractEndpointType[] $manageNameIDService
     *
     * @return void
     */
    protected function setManageNameIDService(array $manageNameIDService): void
    {
        $this->ManageNameIDService = $manageNameIDService;
    }


    /**
     * Add the value to the ManageNameIDService-property
     *
     * @param \SAML2\XML\md\AbstractEndpointType $manageNameIDService
     *
     * @return void
     */
    public function addManageNameIDService(AbstractEndpointType $manageNameIDService): void
    {
        $this->ManageNameIDService[] = $manageNameIDService;
    }


    /**
     * Process an XML element and get its NameIDFormat elements, if any.
     *
     * @param DOMElement $xml An element that may contain NameIDFormat elements.
     *
     * @return array
     */
    public static function getNameIDFormatsFromXML(DOMElement $xml): array
    {
        return Utils::extractStrings($xml, Constants::NS_MD, 'NameIDFormat');
    }


    /**
     * Collect the value of the NameIDFormat-property
     *
     * @return string[]
     */
    public function getNameIDFormat(): array
    {
        return $this->NameIDFormat;
    }


    /**
     * Set the value of the NameIDFormat-property
     *
     * @param string[] $nameIDFormat
     * @return void
     */
    protected function setNameIDFormat(array $nameIDFormat): void
    {
        $this->NameIDFormat = $nameIDFormat;
    }


    /**
     * Add this SSODescriptorType to an EntityDescriptor.
     *
     * @param  \DOMElement $parent The EntityDescriptor we should append this SSODescriptorType to.
     * @return \DOMElement The generated SSODescriptor DOMElement.
     * @throws \Exception
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->ArtifactResolutionService as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->SingleLogoutService as $ep) {
            $ep->toXML($e, 'md:SingleLogoutService');
        }

        foreach ($this->ManageNameIDService as $ep) {
            $ep->toXML($e, 'md:ManageNameIDService');
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:NameIDFormat', false, $this->NameIDFormat);

        return $e;
    }
}
