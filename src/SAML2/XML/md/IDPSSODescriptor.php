<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Attribute;

use function is_bool;

/**
 * Class representing SAML 2 IDPSSODescriptor.
 *
 * @package SimpleSAMLphp
 */
class IDPSSODescriptor extends SSODescriptorType
{
    /**
     * Whether AuthnRequests sent to this IdP should be signed.
     *
     * @var bool|null
     */
    private ?bool $WantAuthnRequestsSigned = null;

    /**
     * List of SingleSignOnService endpoints.
     *
     * Array with SingleSignOnService objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\SingleSignOnService[]
     */
    private array $SingleSignOnService = [];

    /**
     * List of NameIDMappingService endpoints.
     *
     * Array with NameIDMappingService objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\NameIDMappingService[]
     */
    private array $NameIDMappingService = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * Array with AssertionIDRequestService objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[]
     */
    private array $AssertionIDRequestService = [];

    /**
     * List of supported attribute profiles.
     *
     * Array with AttributeProfile objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\AttributeProfile[]
     */
    private array $AttributeProfile = [];

    /**
     * List of supported attributes.
     *
     * Array with \SAML2\XML\saml\Attribute objects.
     *
     * @var \SimpleSAML\SAML2\XML\saml\Attribute[]
     */
    private array $Attribute = [];


    /**
     * Initialize an IDPSSODescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct('md:IDPSSODescriptor', $xml);

        if ($xml === null) {
            return;
        }

        $this->WantAuthnRequestsSigned = Utils::parseBoolean($xml, 'WantAuthnRequestsSigned', null);

        $this->setSingleSignOnService(SingleSignOnService::getChildrenOfClass($xml));
        $this->setNameIDMappingService(NameIDMappingService::getChildrenOfClass($xml));
        $this->setAssertionIDRequestService(AssertionIDRequestService::getChildrenOfClass($xml));
        $this->setAttributeProfile(AttributeProfile::getChildrenOfClass($xml));

        $xpCache = XPath::getXPath($xml);

        /** @var \DOMElement $a */
        foreach (XPath::xpQuery($xml, './saml_assertion:Attribute', $xpCache) as $a) {
            $this->Attribute[] = new Attribute($a);
        }
    }


    /**
     * Collect the value of the WantAuthnRequestsSigned-property
     *
     * @return bool|null
     */
    public function wantAuthnRequestsSigned(): ?bool
    {
        return $this->WantAuthnRequestsSigned;
    }


    /**
     * Set the value of the WantAuthnRequestsSigned-property
     *
     * @param bool|null $flag
     * @return void
     */
    public function setWantAuthnRequestsSigned(bool $flag = null): void
    {
        $this->WantAuthnRequestsSigned = $flag;
    }


    /**
     * Collect the value of the SingleSignOnService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\SingleSignOnService[]
     */
    public function getSingleSignOnService(): array
    {
        return $this->SingleSignOnService;
    }


    /**
     * Set the value of the SingleSignOnService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\SingleSignOnService[] $singleSignOnService
     * @return void
     */
    public function setSingleSignOnService(array $singleSignOnService): void
    {
        Assert::allIsInstanceOf($singleSignOnService, SingleSignOnService::class);
        $this->SingleSignOnService = $singleSignOnService;
    }


    /**
     * Add the value to the SingleSignOnService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\SingleSignOnService $singleSignOnService
     * @return void
     */
    public function addSingleSignOnService(SingleSignOnService $singleSignOnService): void
    {
        $this->SingleSignOnService[] = $singleSignOnService;
    }


    /**
     * Collect the value of the NameIDMappingService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\NameIDMappingService[]
     */
    public function getNameIDMappingService(): array
    {
        return $this->NameIDMappingService;
    }


    /**
     * Set the value of the NameIDMappingService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\NameIDMappingService[] $nameIDMappingService
     * @return void
     */
    public function setNameIDMappingService(array $nameIDMappingService): void
    {
        Assert::allIsInstanceOf($nameIDMappingService, NameIDMappingService::class);
        $this->NameIDMappingService = $nameIDMappingService;
    }


    /**
     * Add the value to the NameIDMappingService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\NameIDMappingService $nameIDMappingService
     * @return void
     */
    public function addNameIDMappingService(NameIDMappingService $nameIDMappingService): void
    {
        $this->NameIDMappingService[] = $nameIDMappingService;
    }


    /**
     * Collect the value of the AssertionIDRequestService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[]
     */
    public function getAssertionIDRequestService(): array
    {
        return $this->AssertionIDRequestService;
    }


    /**
     * Set the value of the AssertionIDRequestService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[] $assertionIDRequestService
     * @return void
     */
    public function setAssertionIDRequestService(array $assertionIDRequestService): void
    {
        Assert::allIsInstanceOf($assertionIDRequestService, AssertionIDRequestService::class);
        $this->AssertionIDRequestService = $assertionIDRequestService;
    }


    /**
     * Add the value to the AssertionIDRequestService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AssertionIDRequestService $assertionIDRequestService
     * @return void
     */
    public function addAssertionIDRequestService(AssertionIDRequestService $assertionIDRequestService): void
    {
        $this->AssertionIDRequestService[] = $assertionIDRequestService;
    }


    /**
     * Collect the value of the AttributeProfile-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AttributeProfile[]
     */
    public function getAttributeProfile(): array
    {
        return $this->AttributeProfile;
    }


    /**
     * Set the value of the AttributeProfile-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AttributeProfile[] $attributeProfile
     * @return void
     */
    public function setAttributeProfile(array $attributeProfile): void
    {
        Assert::allIsInstanceOf($attributeProfile, AttributeProfile::class);
        $this->AttributeProfile = $attributeProfile;
    }


    /**
     * Collect the value of the Attribute-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\Attribute[]
     */
    public function getAttribute(): array
    {
        return $this->Attribute;
    }


    /**
     * Set the value of the Attribute-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[] $attribute
     * @return void
     */
    public function setAttribute(array $attribute): void
    {
        $this->Attribute = $attribute;
    }


    /**
     * Addthe value to the Attribute-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\Attribute $attribute
     * @return void
     */
    public function addAttribute(Attribute $attribute): void
    {
        $this->Attribute[] = $attribute;
    }


    /**
     * Add this IDPSSODescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this IDPSSODescriptor to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        $e = parent::toXML($parent);

        if (is_bool($this->WantAuthnRequestsSigned)) {
            $e->setAttribute('WantAuthnRequestsSigned', $this->WantAuthnRequestsSigned ? 'true' : 'false');
        }

        foreach ($this->SingleSignOnService as $ssos) {
            $ssos->toXML($e);
        }

        foreach ($this->NameIDMappingService as $nidms) {
            $nidms->toXML($e);
        }

        foreach ($this->AssertionIDRequestService as $aidrs) {
            $aidrs->toXML($e);
        }

        foreach ($this->AttributeProfile as $ap) {
            $ap->toXML($e);
        }

        foreach ($this->Attribute as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
