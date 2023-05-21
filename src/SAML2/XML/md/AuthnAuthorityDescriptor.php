<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\Exception\MissingElementException;

/**
 * Class representing SAML 2 metadata AuthnAuthorityDescriptor.
 *
 * @package SimpleSAMLphp
 */
class AuthnAuthorityDescriptor extends RoleDescriptor
{
    /**
     * List of AuthnQueryService endpoints.
     *
     * Array with AuthnQuery objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\AuthnQueryService[]
     */
    private array $AuthnQueryService = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * Array with AssertionIDRequestService objects.
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
        parent::__construct('md:AuthnAuthorityDescriptor', $xml);

        if ($xml === null) {
            return;
        }

        $xpCache = XPath::getXPath($xml);

        $this->setAuthnQueryService(AuthnQueryService::getChildrenOfClass($xml));
        if ($this->getAuthnQueryService() === []) {
            throw new MissingElementException('Must have at least one AuthnQueryService in AuthnAuthorityDescriptor.');
        }

        $this->setAssertionIDRequestService(AssertionIDRequestService::getChildrenOfClass($xml));
        $this->setNameIDFormat(NameIDFormat::getChildrenOfClass($xml));
    }


    /**
     * Collect the value of the AuthnQueryService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AuthnQueryService[]
     */
    public function getAuthnQueryService(): array
    {
        return $this->AuthnQueryService;
    }


    /**
     * Set the value of the AuthnQueryService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AuthnQueryService[] $authnQueryService
     * @return void
     */
    public function setAuthnQueryService(array $authnQueryService): void
    {
        Assert::allIsInstanceOf($authnQueryService, AuthnQueryService::class);
        $this->AuthnQueryService = $authnQueryService;
    }


    /**
     * Add the value to the AuthnQueryService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AuthnQueryService $authnQueryService
     * @return void
     */
    public function addAuthnQueryService(AuthnQueryService $authnQueryService): void
    {
        $this->AuthnQueryService[] = $authnQueryService;
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
     * Add this IDPSSODescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this AuthnAuthorityDescriptor to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->AuthnQueryService);

        $e = parent::toXML($parent);

        foreach ($this->AuthnQueryService as $aqs) {
            $aqs->toXML($e);
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
