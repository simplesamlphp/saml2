<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\Utils;

/**
 * Class representing SAML 2 SPSSODescriptor.
 *
 * @package SimpleSAMLphp
 */
class SPSSODescriptor extends SSODescriptorType
{
    /**
     * Whether this SP signs authentication requests.
     *
     * @var bool|null
     */
    private $AuthnRequestsSigned = null;

    /**
     * Whether this SP wants the Assertion elements to be signed.
     *
     * @var bool|null
     */
    private $WantAssertionsSigned = null;

    /**
     * List of AssertionConsumerService endpoints for this SP.
     *
     * Array with IndexedEndpointType objects.
     *
     * @var \SAML2\XML\md\IndexedEndpointType[]
     */
    private $AssertionConsumerService = [];

    /**
     * List of AttributeConsumingService descriptors for this SP.
     *
     * Array with \SAML2\XML\md\AttributeConsumingService objects.
     *
     * @var \SAML2\XML\md\AttributeConsumingService[]
     */
    private $AttributeConsumingService = [];


    /**
     * Initialize a SPSSODescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        parent::__construct('md:SPSSODescriptor', $xml);

        if ($xml === null) {
            return;
        }

        $this->setAuthnRequestsSigned(Utils::parseBoolean($xml, 'AuthnRequestsSigned', null));
        $this->setWantAssertionsSigned(Utils::parseBoolean($xml, 'WantAssertionsSigned', null));

        foreach (Utils::xpQuery($xml, './saml_metadata:AssertionConsumerService') as $ep) {
            $this->addAssertionConsumerService(new IndexedEndpointType($ep));
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:AttributeConsumingService') as $acs) {
            $this->addAttributeConsumingService(new AttributeConsumingService($acs));
        }
    }


    /**
     * Collect the value of the AuthnRequestsSigned-property
     * @return bool|null
     */
    public function getAuthnRequestsSigned()
    {
        return $this->AuthnRequestsSigned;
    }


    /**
     * Set the value of the AuthnRequestsSigned-property
     * @param bool|null $flag
     * @return void
     */
    public function setAuthnRequestsSigned(bool $flag = null)
    {
        $this->AuthnRequestsSigned = $flag;
    }


    /**
     * Collect the value of the WantAssertionsSigned-property
     * @return bool|null
     */
    public function wantAssertionsSigned()
    {
        return $this->WantAssertionsSigned;
    }


    /**
     * Set the value of the WantAssertionsSigned-property
     * @param bool|null $flag
     * @return void
     */
    public function setWantAssertionsSigned(bool $flag = null)
    {
        $this->WantAssertionsSigned = $flag;
    }


    /**
     * Collect the value of the AssertionConsumerService-property
     * @return array
     */
    public function getAssertionConsumerService() : array
    {
        return $this->AssertionConsumerService;
    }


    /**
     * Set the value of the AssertionConsumerService-property
     * @param array $acs
     * @return void
     */
    public function setAssertionConsumerService(array $acs)
    {
        $this->AssertionConsumerService = $acs;
    }


    /**
     * Add the value to the AssertionConsumerService-property
     * @param \SAML2\XML\md\IndexedEndpointType $acs
     * @return void
     */
    public function addAssertionConsumerService(IndexedEndpointType $acs)
    {
        $this->AssertionConsumerService[] = $acs;
    }


    /**
     * Collect the value of the AttributeConsumingService-property
     * @return array
     */
    public function getAttributeConsumingService() : array
    {
        return $this->AttributeConsumingService;
    }


    /**
     * Add the value to the AttributeConsumingService-property
     * @param \SAML2\XML\md\AttributeConsumingService $acs
     * @return void
     */
    public function addAttributeConsumingService(AttributeConsumingService $acs)
    {
        $this->AttributeConsumingService[] = $acs;
    }


    /**
     * Set the value of the AttributeConsumingService-property
     * @param array $acs
     * @return void
     */
    public function setAttributeConsumingService(array $acs)
    {
        $this->AttributeConsumingService = $acs;
    }


    /**
     * Add this SPSSODescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this SPSSODescriptor to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent) : \DOMElement
    {
        assert(is_null($this->getAuthnRequestsSigned()) || is_bool($this->getAuthnRequestsSigned()));
        assert(is_null($this->wantAssertionsSigned()) || is_bool($this->wantAssertionsSigned()));
        assert(is_array($this->getAssertionConsumerService()));
        assert(is_array($this->getAttributeConsumingService()));

        $e = parent::toXML($parent);

        if ($this->getAuthnRequestsSigned() === true) {
            $e->setAttribute('AuthnRequestsSigned', 'true');
        } elseif ($this->getAuthnRequestsSigned() === false) {
            $e->setAttribute('AuthnRequestsSigned', 'false');
        }

        if ($this->wantAssertionsSigned() === true) {
            $e->setAttribute('WantAssertionsSigned', 'true');
        } elseif ($this->wantAssertionsSigned() === false) {
            $e->setAttribute('WantAssertionsSigned', 'false');
        }

        foreach ($this->getAssertionConsumerService() as $ep) {
            $ep->toXML($e, 'md:AssertionConsumerService');
        }

        foreach ($this->getAttributeConsumingService() as $acs) {
            $acs->toXML($e);
        }

        return $e;
    }
}
