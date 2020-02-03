<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 metadata PDPDescriptor.
 *
 * @package SimpleSAMLphp
 */
final class PDPDescriptor extends AbstractRoleDescriptor
{
    /**
     * List of AuthzService endpoints.
     *
     * @var \SAML2\XML\md\AuthzService[]
     */
    protected $authzServiceEndpoints = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * @var \SAML2\XML\md\AssertionIDRequestService[]
     */
    protected $assertionIDRequestServiceEndpoints = [];

    /**
     * List of supported NameID formats.
     *
     * @var string[]
     */
    protected $nameIDFormats = [];


    /**
     * PDPDescriptor constructor.
     *
     * @param \SAML2\XML\md\AuthzService[] $authServiceEndpoints
     * @param string[] $protocolSupportEnumeration
     * @param \SAML2\XML\md\AssertionIDRequestService[]|null $assertionIDRequestService
     * @param string[]|null $nameIDFormats
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SAML2\XML\md\KeyDescriptor[]|null $keyDescriptors
     * @param \SAML2\XML\md\Organization|null $organization
     * @param \SAML2\XML\md\ContactPerson[]|null $contacts
     */
    public function __construct(
        array $authServiceEndpoints,
        array $protocolSupportEnumeration,
        ?array $assertionIDRequestService = null,
        ?array $nameIDFormats = null,
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        ?array $keyDescriptors = null,
        ?Organization $organization = null,
        ?array $contacts = null
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
        $this->setAuthzServiceEndpoints($authServiceEndpoints);
        $this->setAssertionIDRequestServices($assertionIDRequestService);
        $this->setNameIDFormats($nameIDFormats);
    }


    /**
     * Initialize an IDPSSODescriptor from a given XML document.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return \SAML2\XML\md\PDPDescriptor
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'PDPDescriptor');
        Assert::same($xml->namespaceURI, PDPDescriptor::NS);

        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor');

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.');

        return new self(
            AuthzService::getChildrenOfClass($xml),
            preg_split('/[\s]+/', trim(self::getAttribute($xml, 'protocolSupportEnumeration'))),
            AssertionIDRequestService::getChildrenOfClass($xml),
            Utils::extractStrings($xml, Constants::NS_MD, 'NameIDFormat'),
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? Utils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getAttribute($xml, 'errorURL', null),
            KeyDescriptor::getChildrenOfClass($xml),
            !empty($orgs) ? $orgs[0] : null,
            ContactPerson::getChildrenOfClass($xml)
        );
    }


    /**
     * Get the AuthzService endpoints of this PDPDescriptor
     *
     * @return \SAML2\XML\md\AuthzService[]
     */
    public function getAuthzServiceEndpoints(): array
    {
        return $this->authzServiceEndpoints;
    }


    /**
     * Set the AuthzService endpoints for this PDPDescriptor
     *
     * @param \SAML2\XML\md\AuthzService[] $authzServices
     */
    protected function setAuthzServiceEndpoints(array $authzServices = []): void
    {
        Assert::minCount($authzServices, 1, 'At least one md:AuthzService endpoint must be present.');
        Assert::allIsInstanceOf(
            $authzServices,
            AuthzService::class,
            'All md:AuthzService endpoints must be an instance of AuthzService.'
        );
        $this->authzServiceEndpoints = $authzServices;
    }


    /**
     * Get the AssertionIDRequestService endpoints of this PDPDescriptor
     *
     * @return \SAML2\XML\md\AssertionIDRequestService[]
     */
    public function getAssertionIDRequestServices(): array
    {
        return $this->assertionIDRequestServiceEndpoints;
    }


    /**
     * Set the AssertionIDRequestService endpoints for this PDPDescriptor
     *
     * @param \SAML2\XML\md\AssertionIDRequestService[] $assertionIDRequestServices
     */
    public function setAssertionIDRequestServices(?array $assertionIDRequestServices): void
    {
        if ($assertionIDRequestServices === null) {
            return;
        }
        Assert::allIsInstanceOf(
            $assertionIDRequestServices,
            AssertionIDRequestService::class,
            'All md:AssertionIDRequestService endpoints must be an instance of AssertionIDRequestService.'
        );
        $this->assertionIDRequestServiceEndpoints = $assertionIDRequestServices;
    }


    /**
     * Get the NameIDFormats supported by this PDPDescriptor
     *
     * @return string[]
     */
    public function getNameIDFormats(): array
    {
        return $this->nameIDFormats;
    }


    /**
     * Set the NameIDFormats supported by this PDPDescriptor
     *
     * @param string[] $nameIDFormats
     */
    public function setNameIDFormats(?array $nameIDFormats): void
    {
        if ($nameIDFormats === null) {
            return;
        }
        Assert::allStringNotEmpty($nameIDFormats, 'All NameIDFormat must be a non-empty string.');
        $this->nameIDFormats = $nameIDFormats;
    }


    /**
     * Add this PDPDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this IDPSSODescriptor to.
     * @return \DOMElement
     * @throws \Exception
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->authzServiceEndpoints as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->assertionIDRequestServiceEndpoints as $ep) {
            $ep->toXML($e);
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:NameIDFormat', false, $this->nameIDFormats);

        return $e;
    }
}
