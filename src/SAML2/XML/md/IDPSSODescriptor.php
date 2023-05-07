<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\XML\Utils as XMLUtils;

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
     * Array with EndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    private array $SingleSignOnService = [];

    /**
     * List of NameIDMappingService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    private array $NameIDMappingService = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    private array $AssertionIDRequestService = [];

    /**
     * List of supported attribute profiles.
     *
     * Array with strings.
     *
     * @var array
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

        $xpCache = XPath::getXPath($xml);

        /** @var \DOMElement $ep */
        foreach (XPath::xpQuery($xml, './saml_metadata:SingleSignOnService', $xpCache) as $ep) {
            $this->SingleSignOnService[] = new EndpointType($ep);
        }

        /** @var \DOMElement $ep */
        foreach (XPath::xpQuery($xml, './saml_metadata:NameIDMappingService', $xpCache) as $ep) {
            $this->NameIDMappingService[] = new EndpointType($ep);
        }

        /** @var \DOMElement $ep */
        foreach (XPath::xpQuery($xml, './saml_metadata:AssertionIDRequestService', $xpCache) as $ep) {
            $this->AssertionIDRequestService[] = new EndpointType($ep);
        }

        $this->AttributeProfile = XMLUtils::extractStrings($xml, C::NS_MD, 'AttributeProfile');

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
     * @return \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    public function getSingleSignOnService(): array
    {
        return $this->SingleSignOnService;
    }


    /**
     * Set the value of the SingleSignOnService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType[] $singleSignOnService
     * @return void
     */
    public function setSingleSignOnService(array $singleSignOnService): void
    {
        $this->SingleSignOnService = $singleSignOnService;
    }


    /**
     * Add the value to the SingleSignOnService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType $singleSignOnService
     * @return void
     */
    public function addSingleSignOnService(EndpointType $singleSignOnService): void
    {
        $this->SingleSignOnService[] = $singleSignOnService;
    }


    /**
     * Collect the value of the NameIDMappingService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    public function getNameIDMappingService(): array
    {
        return $this->NameIDMappingService;
    }


    /**
     * Set the value of the NameIDMappingService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType[] $nameIDMappingService
     * @return void
     */
    public function setNameIDMappingService(array $nameIDMappingService): void
    {
        $this->NameIDMappingService = $nameIDMappingService;
    }


    /**
     * Add the value to the NameIDMappingService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType $nameIDMappingService
     * @return void
     */
    public function addNameIDMappingService(EndpointType $nameIDMappingService): void
    {
        $this->NameIDMappingService[] = $nameIDMappingService;
    }


    /**
     * Collect the value of the AssertionIDRequestService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    public function getAssertionIDRequestService(): array
    {
        return $this->AssertionIDRequestService;
    }


    /**
     * Set the value of the AssertionIDRequestService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType[] $assertionIDRequestService
     * @return void
     */
    public function setAssertionIDRequestService(array $assertionIDRequestService): void
    {
        $this->AssertionIDRequestService = $assertionIDRequestService;
    }


    /**
     * Add the value to the AssertionIDRequestService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType $assertionIDRequestService
     * @return void
     */
    public function addAssertionIDRequestService(EndpointType $assertionIDRequestService): void
    {
        $this->AssertionIDRequestService[] = $assertionIDRequestService;
    }


    /**
     * Collect the value of the AttributeProfile-property
     * @return array
     */
    public function getAttributeProfile(): array
    {
        return $this->AttributeProfile;
    }


    /**
     * Set the value of the AttributeProfile-property
     *
     * @param array $attributeProfile
     * @return void
     */
    public function setAttributeProfile(array $attributeProfile): void
    {
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

        foreach ($this->SingleSignOnService as $ep) {
            $ep->toXML($e, 'md:SingleSignOnService');
        }

        foreach ($this->NameIDMappingService as $ep) {
            $ep->toXML($e, 'md:NameIDMappingService');
        }

        foreach ($this->AssertionIDRequestService as $ep) {
            $ep->toXML($e, 'md:AssertionIDRequestService');
        }

        XMLUtils::addStrings($e, C::NS_MD, 'md:AttributeProfile', false, $this->AttributeProfile);

        foreach ($this->Attribute as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
