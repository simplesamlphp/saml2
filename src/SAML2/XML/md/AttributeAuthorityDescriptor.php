<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\XML\Exception\MissingElementException;

/**
 * Class representing SAML 2 metadata AttributeAuthorityDescriptor.
 *
 * @package SimpleSAMLphp
 */
class AttributeAuthorityDescriptor extends RoleDescriptor
{
    /**
     * List of AttributeService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    private array $AttributeService = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    private array $AssertionIDRequestService = [];

    /**
     * List of supported NameID formats.
     *
     * Array with NameIDFormat objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    private array $NameIDFormat = [];

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
     * Array with \SimpleSAML\SAML2\XML\saml\Attribute objects.
     *
     * @var \SimpleSAML\SAML2\XML\saml\Attribute[]
     */
    private array $Attribute = [];


    /**
     * Initialize an IDPSSODescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct('md:AttributeAuthorityDescriptor', $xml);

        if ($xml === null) {
            return;
        }

        $xpCache = XPath::getXPath($xml);

        /** @var \DOMElement $ep */
        foreach (XPath::xpQuery($xml, './saml_metadata:AttributeService', $xpCache) as $ep) {
            $this->addAttributeService(new EndpointType($ep));
        }
        if ($this->getAttributeService() === []) {
            throw new MissingElementException(
                'Must have at least one AttributeService in AttributeAuthorityDescriptor.'
            );
        }

        /** @var \DOMElement $ep */
        foreach (XPath::xpQuery($xml, './saml_metadata:AssertionIDRequestService', $xpCache) as $ep) {
            $this->addAssertionIDRequestService(new EndpointType($ep));
        }

        $this->setNameIDFormat(NameIDFormat::getChildrenOfClass($xml));

        $this->setAttributeProfile(AttributeProfile::getChildrenOfClass($xml));

        /** @var \DOMElement $a */
        foreach (XPath::xpQuery($xml, './saml_assertion:Attribute', $xpCache) as $a) {
            $this->addAttribute(new Attribute($a));
        }
    }


    /**
     * Collect the value of the AttributeService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    public function getAttributeService(): array
    {
        return $this->AttributeService;
    }


    /**
     * Set the value of the AttributeService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType[] $attributeService
     * @return void
     */
    public function setAttributeService(array $attributeService): void
    {
        $this->AttributeService = $attributeService;
    }


    /**
     * Add the value to the AttributeService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType $attributeService
     * @return void
     */
    public function addAttributeService(EndpointType $attributeService): void
    {
        $this->AttributeService[] = $attributeService;
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
     * Add the value to the Attribute-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\Attribute $attribute
     * @return void
     */
    public function addAttribute(Attribute $attribute): void
    {
        $this->Attribute[] = $attribute;
    }


    /**
     * Add this AttributeAuthorityDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this IDPSSODescriptor to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->AttributeService);

        $e = parent::toXML($parent);

        foreach ($this->AttributeService as $ep) {
            $ep->toXML($e, 'md:AttributeService');
        }

        foreach ($this->AssertionIDRequestService as $ep) {
            $ep->toXML($e, 'md:AssertionIDRequestService');
        }

        foreach ($this->NameIDFormat as $nid) {
            $nid->toXML($e);
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
