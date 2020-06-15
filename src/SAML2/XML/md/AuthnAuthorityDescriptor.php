<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Exception\TooManyElementsException;
use SAML2\Utils;
use SAML2\XML\ds\Signature;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SAML 2 metadata AuthnAuthorityDescriptor.
 *
 * @package simplesamlphp/saml2
 */
final class AuthnAuthorityDescriptor extends AbstractRoleDescriptor
{
    /**
     * List of AuthnQueryService endpoints.
     *
     * @var \SAML2\XML\md\AbstractEndpointType[]
     */
    protected $AuthnQueryServices = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * @var \SAML2\XML\md\AbstractEndpointType[]
     */
    protected $AssertionIDRequestServices = [];

    /**
     * List of supported NameID formats.
     *
     * Array of strings.
     *
     * @var string[]
     */
    protected $NameIDFormats = [];


    /**
     * AuthnAuthorityDescriptor constructor.
     *
     * @param array $authnQueryServices
     * @param array $protocolSupportEnumeration
     * @param array|null $assertionIDRequestServices
     * @param array|null $nameIDFormats
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SAML2\XML\md\Organization|null $organization
     * @param array $keyDescriptors
     * @param array $contacts
     */
    public function __construct(
        array $authnQueryServices,
        array $protocolSupportEnumeration,
        array $assertionIDRequestServices = [],
        array $nameIDFormats = [],
        string $ID = null,
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
        $this->setAuthnQueryServices($authnQueryServices);
        $this->setAssertionIDRequestService($assertionIDRequestServices);
        $this->setNameIDFormat($nameIDFormats);
    }


    /**
     * Initialize an IDPSSODescriptor from an existing XML document.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return self
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SAML2\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SAML2\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnAuthorityDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnAuthorityDescriptor::NS, InvalidDOMElementException::class);

        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');

        $authnQueryServices = AuthnQueryServices::getChildrenOfClass($xml);
        $assertionIDRequestServices = AssertionIDRequestServices::getChildrenOfClass($xml);

        $nameIDFormats = Utils::extractStrings($xml, Constants::NS_MD, 'NameIDFormat');

        $validUntil = self::getAttribute($xml, 'validUntil', null);

        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor', TooManyElementsException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.', TooManyElementsException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $authority = new self(
            $authnQueryServices,
            preg_split('/[\s]+/', trim($protocols)),
            $assertionIDRequestServices,
            $nameIDFormats,
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? Utils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getAttribute($xml, 'errorURL', null),
            !empty($orgs) ? $orgs[0] : null,
            KeyDescriptor::getChildrenOfClass($xml),
            ContactPerson::getChildrenOfClass($xml)
        );
        if (!empty($signature)) {
            $authority->setSignature($signature[0]);
        }
        return $authority;
    }


    /**
     * Collect the AuthnQueryService endpoints
     *
     * @return \SAML2\XML\md\AbstractEndpointType[]
     */
    public function getAuthnQueryServices(): array
    {
        return $this->AuthnQueryServices;
    }


    /**
     * Set the AuthnQueryService endpoints
     *
     * @param \SAML2\XML\md\AbstractEndpointType[] $authnQueryServices
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setAuthnQueryServices(array $authnQueryServices): void
    {
        Assert::minCount($authnQueryServices, 1, 'Missing at least one AuthnQueryService in AuthnAuthorityDescriptor.');
        Assert::allIsInstanceOf(
            $authnQueryServices,
            AbstractEndpointType::class,
            'AuthnQueryService must be an instance of EndpointType'
        );
        $this->AuthnQueryServices = $authnQueryServices;
    }


    /**
     * Collect the AssertionIDRequestService endpoints
     *
     * @return \SAML2\XML\md\AbstractEndpointType[]
     */
    public function getAssertionIDRequestServices(): array
    {
        return $this->AssertionIDRequestServices;
    }


    /**
     * Set the AssertionIDRequestService endpoints
     *
     * @param \SAML2\XML\md\AbstractEndpointType[] $assertionIDRequestServices
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setAssertionIDRequestService(?array $assertionIDRequestServices = []): void
    {
        Assert::allIsInstanceOf(
            $assertionIDRequestServices,
            AbstractEndpointType::class,
            'AssertionIDRequestServices must be an instance of EndpointType'
        );
        $this->AssertionIDRequestServices = $assertionIDRequestServices;
    }


    /**
     * Collect the values of the NameIDFormat
     *
     * @return string[]
     */
    public function getNameIDFormats(): array
    {
        return $this->NameIDFormats;
    }


    /**
     * Set the values of the NameIDFormat
     *
     * @param string[] $nameIDFormats
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setNameIDFormat(?array $nameIDFormats): void
    {
        if ($nameIDFormats === null) {
            return;
        }
        Assert::allStringNotEmpty($nameIDFormats, 'NameIDFormat cannot be an empty string.');
        $this->NameIDFormats = $nameIDFormats;
    }


    /**
     * Add this IDPSSODescriptor to an EntityDescriptor.
     *
     * @param \DOMElement|null $parent The EntityDescriptor we should append this AuthnAuthorityDescriptor to.
     *
     * @return \DOMElement
     * @throws \Exception
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->AuthnQueryServices as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->AssertionIDRequestServices as $ep) {
            $ep->toXML($e);
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:NameIDFormat', false, $this->NameIDFormats);

        return $this->signElement($e);
    }
}
