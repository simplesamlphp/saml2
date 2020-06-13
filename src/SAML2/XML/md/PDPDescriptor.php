<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Utils;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SAML 2 metadata PDPDescriptor.
 *
 * @package simplesamlphp/saml2
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
     * @param \SAML2\XML\md\AssertionIDRequestService[] $assertionIDRequestService
     * @param string[] $nameIDFormats
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SAML2\XML\md\Organization|null $organization
     * @param \SAML2\XML\md\KeyDescriptor[] $keyDescriptors
     * @param \SAML2\XML\md\ContactPerson[] $contacts
     */
    public function __construct(
        array $authServiceEndpoints,
        array $protocolSupportEnumeration,
        array $assertionIDRequestService = [],
        array $nameIDFormats = [],
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        ?Organization $organization = null,
        array $keyDescriptors = [],
        array $contacts = []
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
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'PDPDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, PDPDescriptor::NS, InvalidDOMElementException::class);

        /** @var string $protocols */
        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');

        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor');

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.');

        return new self(
            AuthzService::getChildrenOfClass($xml),
            preg_split('/[\s]+/', trim($protocols)),
            AssertionIDRequestService::getChildrenOfClass($xml),
            Utils::extractStrings($xml, Constants::NS_MD, 'NameIDFormat'),
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? Utils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getAttribute($xml, 'errorURL', null),
            !empty($orgs) ? $orgs[0] : null,
            KeyDescriptor::getChildrenOfClass($xml),
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
     * @return void
     * @throws \InvalidArgumentException
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
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setAssertionIDRequestServices(array $assertionIDRequestServices): void
    {
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
    public function setNameIDFormats(array $nameIDFormats): void
    {
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

        return $this->signElement($e);
    }
}
