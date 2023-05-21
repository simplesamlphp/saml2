<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\Exception\MissingElementException;

/**
 * Class representing SAML 2 metadata PDPDescriptor.
 *
 * @package SimpleSAMLphp
 */
class PDPDescriptor extends RoleDescriptor
{
    /**
     * List of AuthzService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    private array $AuthzService = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[]
     */
    private array $AssertionIDRequestService = [];

    /**
     * List of supported NameID formats.
     *
     * Array of strings.
     *
     * @var \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    private array $NameIDFormat = [];


    /**
     * Initialize an IDPSSODescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct('md:PDPDescriptor', $xml);

        if ($xml === null) {
            return;
        }

        $xpCache = XPath::getXPath($xml);

        /** @var \DOMElement $ep */
        foreach (XPath::xpQuery($xml, './saml_metadata:AuthzService', $xpCache) as $ep) {
            $this->AuthzService[] = new EndpointType($ep);
        }
        if ($this->getAuthzService() !== []) {
            throw new MissingElementException('Must have at least one AuthzService in PDPDescriptor.');
        }

        $this->AssertionIDRequestService = AssertionIDRequestService::getChildrenOfClass($xml);
        $this->NameIDFormat = NameIDFormat::getChildrenOfClass($xml);
    }


    /**
     * Collect the value of the AuthzService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\EndpointType[]
     */
    public function getAuthzService(): array
    {
        return $this->AuthzService;
    }


    /**
     * Set the value of the AuthzService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType[] $authzService
     * @return void
     */
    public function setAuthzService(array $authzService = []): void
    {
        $this->AuthzService = $authzService;
    }


    /**
     * Add the value to the AuthzService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\EndpointType $authzService
     * @return void
     */
    public function addAuthzService(EndpointType $authzService): void
    {
        $this->AuthzService[] = $authzService;
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
        Assertion::allIsInstanceOf($assertionIDRequestService, AssertionIDRequestService::class);
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
     * Add this PDPDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this IDPSSODescriptor to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->AuthzService);

        $e = parent::toXML($parent);

        foreach ($this->AuthzService as $ep) {
            $ep->toXML($e, 'md:AuthzService');
        }

        foreach ($this->AssertionIDRequestService as $aidrs) {
            $aidrs->toXML($e);
        }

        foreach ($this->NameIDFormat as $nid) {
            $nid->toXML($e);
        }

        return $e;
    }
}
