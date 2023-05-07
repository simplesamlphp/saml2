<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;

use function is_bool;

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
    private ?bool $AuthnRequestsSigned = null;

    /**
     * Whether this SP wants the Assertion elements to be signed.
     *
     * @var bool|null
     */
    private ?bool $WantAssertionsSigned = null;

    /**
     * List of AssertionConsumerService endpoints for this SP.
     *
     * Array with IndexedEndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\IndexedEndpointType[]
     */
    private array $AssertionConsumerService = [];

    /**
     * List of AttributeConsumingService descriptors for this SP.
     *
     * Array with \SimpleSAML\SAML2\XML\md\AttributeConsumingService objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\AttributeConsumingService[]
     */
    private array $AttributeConsumingService = [];


    /**
     * Initialize a SPSSODescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct('md:SPSSODescriptor', $xml);

        if ($xml === null) {
            return;
        }

        $this->AuthnRequestsSigned = Utils::parseBoolean($xml, 'AuthnRequestsSigned', null);
        $this->WantAssertionsSigned = Utils::parseBoolean($xml, 'WantAssertionsSigned', null);

        $xpCache = XPath::getXPath($xml);
        /** @var \DOMElement $ep */
        foreach (XPath::xpQuery($xml, './saml_metadata:AssertionConsumerService', $xpCache) as $ep) {
            $this->AssertionConsumerService[] = new IndexedEndpointType($ep);
        }

        /** @var \DOMElement $acs */
        foreach (XPath::xpQuery($xml, './saml_metadata:AttributeConsumingService', $xpCache) as $acs) {
            $this->AttributeConsumingService[] = new AttributeConsumingService($acs);
        }
    }


    /**
     * Collect the value of the AuthnRequestsSigned-property
     *
     * @return bool|null
     */
    public function getAuthnRequestsSigned(): ?bool
    {
        return $this->AuthnRequestsSigned;
    }


    /**
     * Set the value of the AuthnRequestsSigned-property
     *
     * @param bool|null $flag
     * @return void
     */
    public function setAuthnRequestsSigned(bool $flag = null): void
    {
        $this->AuthnRequestsSigned = $flag;
    }


    /**
     * Collect the value of the WantAssertionsSigned-property
     *
     * @return bool|null
     */
    public function wantAssertionsSigned(): ?bool
    {
        return $this->WantAssertionsSigned;
    }


    /**
     * Set the value of the WantAssertionsSigned-property
     *
     * @param bool|null $flag
     * @return void
     */
    public function setWantAssertionsSigned(bool $flag = null): void
    {
        $this->WantAssertionsSigned = $flag;
    }


    /**
     * Collect the value of the AssertionConsumerService-property
     *
     * @return array
     */
    public function getAssertionConsumerService(): array
    {
        return $this->AssertionConsumerService;
    }


    /**
     * Set the value of the AssertionConsumerService-property
     *
     * @param array $acs
     * @return void
     */
    public function setAssertionConsumerService(array $acs): void
    {
        $this->AssertionConsumerService = $acs;
    }


    /**
     * Add the value to the AssertionConsumerService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\IndexedEndpointType $acs
     * @return void
     */
    public function addAssertionConsumerService(IndexedEndpointType $acs): void
    {
        $this->AssertionConsumerService[] = $acs;
    }


    /**
     * Collect the value of the AttributeConsumingService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AttributeConsumingService[]
     */
    public function getAttributeConsumingService(): array
    {
        return $this->AttributeConsumingService;
    }


    /**
     * Add the value to the AttributeConsumingService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AttributeConsumingService $acs
     * @return void
     */
    public function addAttributeConsumingService(AttributeConsumingService $acs): void
    {
        $this->AttributeConsumingService[] = $acs;
    }


    /**
     * Set the value of the AttributeConsumingService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AttributeConsumingService[] $acs
     * @return void
     */
    public function setAttributeConsumingService(array $acs): void
    {
        $this->AttributeConsumingService = $acs;
    }


    /**
     * Add this SPSSODescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this SPSSODescriptor to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        $e = parent::toXML($parent);

        if (is_bool($this->AuthnRequestsSigned)) {
            $e->setAttribute('AuthnRequestsSigned', $this->AuthnRequestsSigned ? 'true' : 'false');
        }

        if (is_bool($this->WantAssertionsSigned)) {
            $e->setAttribute('WantAssertionsSigned', $this->WantAssertionsSigned ? 'true' : 'false');
        }

        foreach ($this->AssertionConsumerService as $ep) {
            $ep->toXML($e, 'md:AssertionConsumerService');
        }

        foreach ($this->AttributeConsumingService as $acs) {
            $acs->toXML($e);
        }

        return $e;
    }
}
