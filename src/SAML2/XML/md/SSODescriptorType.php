<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;

/**
 * Class representing SAML 2 SSODescriptorType.
 *
 * @package SimpleSAMLphp
 */
abstract class SSODescriptorType extends RoleDescriptor
{
    /**
     * List of ArtifactResolutionService endpoints.
     *
     * Array with ArtifactResolutionService objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\ArtifactResolutionService[]
     */
    private array $ArtifactResolutionService = [];

    /**
     * List of SingleLogoutService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    private array $SingleLogoutService = [];

    /**
     * List of ManageNameIDService endpoints.
     *
     * Array with ManageNameIDService objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\ManageNameIDService[]
     */
    private array $ManageNameIDService = [];

    /**
     * List of supported NameID formats.
     *
     * Array of strings.
     *
     * @var \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    private array $NameIDFormat = [];


    /**
     * Initialize a SSODescriptor.
     *
     * @param string $elementName The name of this element.
     * @param \DOMElement|null $xml The XML element we should load.
     */
    protected function __construct(string $elementName, DOMElement $xml = null)
    {
        parent::__construct($elementName, $xml);

        if ($xml === null) {
            return;
        }

        $xpCache = XPath::getXPath($xml);

        foreach (ArtifactResolutionService::getChildrenOfClass($xml) as $ars) {
            $this->addArtifactResolutionService($ars);
        }

        /** @var \DOMElement $ep */
        foreach (XPath::xpQuery($xml, './saml_metadata:SingleLogoutService', $xpCache) as $ep) {
            $this->addSingleLogoutService(new EndpointType($ep));
        }

        $this->setManageNameIDService(ManageNameIDService::getChildrenOfClass($xml));
        $this->setNameIDFormat(NameIDFormat::getChildrenOfClass($xml));
    }


    /**
     * Collect the value of the ArtifactResolutionService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\ArtifactResolutionService[]
     */
    public function getArtifactResolutionService(): array
    {
        return $this->ArtifactResolutionService;
    }


    /**
     * Set the value of the ArtifactResolutionService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\ArtifactResolutionService[] $artifactResolutionService
     * @return void
     */
    public function setArtifactResolutionService(array $artifactResolutionService): void
    {
        Assert::allIsInstanceOf($artifactResolutionService, ArtifactResolutionService::class);
        $this->ArtifactResolutionService = $artifactResolutionService;
    }


    /**
     * Add the value to the ArtifactResolutionService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\ArtifactResolutionService $artifactResolutionService
     * @return void
     */
    public function addArtifactResolutionService(ArtifactResolutionService $artifactResolutionService): void
    {
        $this->ArtifactResolutionService[] = $artifactResolutionService;
    }


    /**
     * Collect the value of the SingleLogoutService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    public function getSingleLogoutService(): array
    {
        return $this->SingleLogoutService;
    }


    /**
     * Set the value of the SingleLogoutService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType[] $singleLogoutService
     * @return void
     */
    public function setSingleLogoutService(array $singleLogoutService): void
    {
        $this->SingleLogoutService = $singleLogoutService;
    }


    /**
     * Add the value to the SingleLogoutService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType $singleLogoutService
     * @return void
     */
    public function addSingleLogoutService(EndpointType $singleLogoutService): void
    {
        $this->SingleLogoutService[] = $singleLogoutService;
    }


    /**
     * Collect the value of the ManageNameIDService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\ManageNameIDService[]
     */
    public function getManageNameIDService(): array
    {
        return $this->ManageNameIDService;
    }


    /**
     * Set the value of the ManageNameIDService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\ManageNameIDService[] $manageNameIDService
     * @return void
     */
    public function setManageNameIDService(array $manageNameIDService): void
    {
        Assert::allIsInstanceOf($manageNameIDService, ManageNameIDService::class);
        $this->ManageNameIDService = $manageNameIDService;
    }


    /**
     * Add the value to the ManageNameIDService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\ManageNameIDService $manageNameIDService
     * @return void
     */
    public function addManageNameIDService(ManageNameIDService $manageNameIDService): void
    {
        $this->ManageNameIDService[] = $manageNameIDService;
    }


    /**
     * Collect the value of the NameIDFormat-property
     *
     * @return \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    public function getNameIDFormat(): array
    {
        return $this->NameIDFormat;
    }


    /**
     * Set the value of the NameIDFormat-property
     *
     * @param \SimpleSAML\SAML2\XML\md\NameIDFormat[] $nameIDFormat
     * @return void
     */
    public function setNameIDFormat(array $nameIDFormat): void
    {
        Assert::allIsInstanceOf($nameIDFormat, NameIDFormat::class);
        $this->NameIDFormat = $nameIDFormat;
    }


    /**
     * Add this SSODescriptorType to an EntityDescriptor.
     *
     * @param  \DOMElement $parent The EntityDescriptor we should append this SSODescriptorType to.
     * @return \DOMElement The generated SSODescriptor DOMElement.
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->ArtifactResolutionService as $ars) {
            $ars->toXML($e);
        }

        foreach ($this->SingleLogoutService as $ep) {
            $ep->toXML($e, 'md:SingleLogoutService');
        }

        foreach ($this->ManageNameIDService as $mnids) {
            $mnids->toXML($e);
        }

        foreach ($this->NameIDFormat as $nid) {
            $nid->toXML($e);
        }

        return $e;
    }
}
