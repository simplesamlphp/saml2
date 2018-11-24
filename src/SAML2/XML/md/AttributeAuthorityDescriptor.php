<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\saml\Attribute;

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
     * @var \SAML2\XML\md\EndpointType[]
     */
    public $AttributeService = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SAML2\XML\md\EndpointType[]
     */
    public $AssertionIDRequestService = [];

    /**
     * List of supported NameID formats.
     *
     * Array of strings.
     *
     * @var string[]
     */
    public $NameIDFormat = [];

    /**
     * List of supported attribute profiles.
     *
     * Array with strings.
     *
     * @var array
     */
    public $AttributeProfile = [];

    /**
     * List of supported attributes.
     *
     * Array with \SAML2\XML\saml\Attribute objects.
     *
     * @var \SAML2\XML\saml\Attribute[]
     */
    public $Attribute = [];

    /**
     * Initialize an IDPSSODescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        parent::__construct('md:AttributeAuthorityDescriptor', $xml);

        if ($xml === null) {
            return;
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:AttributeService') as $ep) {
            $this->addAttributeService(new EndpointType($ep));
        }
        if ($this->getAttributeService() === []) {
            throw new \Exception('Must have at least one AttributeService in AttributeAuthorityDescriptor.');
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:AssertionIDRequestService') as $ep) {
            $this->addAssertionIDRequestService(new EndpointType($ep));
        }

        $this->setNameIDFormat(Utils::extractStrings($xml, Constants::NS_MD, 'NameIDFormat'));

        $this->setAttributeProfile(Utils::extractStrings($xml, Constants::NS_MD, 'AttributeProfile'));

        foreach (Utils::xpQuery($xml, './saml_assertion:Attribute') as $a) {
            $this->addAttribute(new Attribute($a));
        }
    }

    /**
     * Collect the value of the AttributeService-property
     * @return \SAML2\XML\md\EndpointType[]
     */
    public function getAttributeService()
    {
        return $this->AttributeService;
    }

    /**
     * Set the value of the AttributeService-property
     * @param \SAML2\XML\md\EndpointType[] $attributeService
     */
    public function setAttributeService(array $attributeService)
    {
        $this->AttributeService = $attributeService;
    }

    /**
     * Add the value to the AttributeService-property
     * @param \SAML2\XML\md\EndpointType $attributeService
     */
    public function addAttributeService(EndpointType $attributeService)
    {
        assert($attributeService instanceof EndpointType);
        $this->AttributeService[] = $attributeService;
    }

    /**
     * Collect the value of the NameIDFormat-property
     * @return string[]
     */
    public function getNameIDFormat()
    {
        return $this->NameIDFormat;
    }

    /**
     * Set the value of the NameIDFormat-property
     * @param string[] $nameIDFormat
     */
    public function setNameIDFormat(array $nameIDFormat)
    {
        $this->NameIDFormat = $nameIDFormat;
    }

    /**
     * Collect the value of the AssertionIDRequestService-property
     * @return \SAML2\XML\md\EndpointType[]
     */
    public function getAssertionIDRequestService()
    {
        return $this->AssertionIDRequestService;
    }

    /**
     * Set the value of the AssertionIDRequestService-property
     * @param \SAML2\XML\md\EndpointType[] $assertionIDRequestService
     */
    public function setAssertionIDRequestService(array $assertionIDRequestService)
    {
        $this->AssertionIDRequestService = $assertionIDRequestService;
    }

    /**
     * Add the value to the AssertionIDRequestService-property
     * @param \SAML2\XML\md\EndpointType $assertionIDRequestService
     */
    public function addAssertionIDRequestService(EndpointType $assertionIDRequestService)
    {
        assert($assertionIDRequestService instanceof EndpointType);
        $this->AssertionIDRequestService[] = $assertionIDRequestService;
    }

    /**
     * Collect the value of the AttributeProfile-property
     * @return string[]
     */
    public function getAttributeProfile()
    {
        return $this->AttributeProfile;
    }

    /**
     * Set the value of the AttributeProfile-property
     * @param string[] $attributeProfile
     */
    public function setAttributeProfile(array $attributeProfile)
    {
        $this->AttributeProfile = $attributeProfile;
    }

    /**
     * Collect the value of the Attribute-property
     * @return \SAML2\XML\saml\Attribute[]
     */
    public function getAttribute()
    {
        return $this->Attribute;
    }

    /**
     * Set the value of the Attribute-property
     * @param \SAML2\XML\saml\Attribute[] $attribute
     */
    public function setAttribute(array $attribute)
    {
        $this->Attribute = $attribute;
    }

    /**
     * Add the value to the Attribute-property
     * @param \SAML2\XML\saml\Attribute $attribute
     */
    public function addAttribute(Attribute $attribute)
    {
        assert($attribute instanceof Attribute);
        $this->Attribute[] = $attribute;
    }

    /**
     * Add this AttributeAuthorityDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this IDPSSODescriptor to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert(is_array($attributeService = $this->getAttributeService()));
        assert(!empty($attributeService));
        assert(is_array($this->getAssertionIDRequestService()));
        assert(is_array($this->getNameIDFormat()));
        assert(is_array($this->getAttributeProfile()));
        assert(is_array($this->Attribute));

        $e = parent::toXML($parent);

        foreach ($this->getAttributeService() as $ep) {
            $ep->toXML($e, 'md:AttributeService');
        }

        foreach ($this->getAssertionIDRequestService() as $ep) {
            $ep->toXML($e, 'md:AssertionIDRequestService');
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:NameIDFormat', false, $this->getNameIDFormat());

        Utils::addStrings($e, Constants::NS_MD, 'md:AttributeProfile', false, $this->getAttributeProfile());

        foreach ($this->getAttribute() as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
