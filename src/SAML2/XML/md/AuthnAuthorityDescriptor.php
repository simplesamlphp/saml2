<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function preg_split;

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
     * @var \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    protected array $AuthnQueryServices = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * @var \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    protected array $AssertionIDRequestServices = [];

    /**
     * List of supported NameID formats.
     *
     * Array of strings.
     *
     * @var \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    protected array $NameIDFormats = [];


    /**
     * AuthnAuthorityDescriptor constructor.
     *
     * @param array $authnQueryServices
     * @param array $protocolSupportEnumeration
     * @param array $assertionIDRequestServices
     * @param array $nameIDFormats
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
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
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AuthnAuthorityDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnAuthorityDescriptor::NS, InvalidDOMElementException::class);

        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');

        $authnQueryServices = AuthnQueryService::getChildrenOfClass($xml);
        $assertionIDRequestServices = AssertionIDRequestService::getChildrenOfClass($xml);
        $nameIDFormats = NameIDFormat::getChildrenOfClass($xml);

        $validUntil = self::getAttribute($xml, 'validUntil', null);

        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor', TooManyElementsException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.', TooManyElementsException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $authority = new static(
            $authnQueryServices,
            preg_split('/[\s]+/', trim($protocols)),
            $assertionIDRequestServices,
            $nameIDFormats,
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? XMLUtils::xsDateTimeToTimestamp($validUntil) : null,
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
     * @return \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    public function getAuthnQueryServices(): array
    {
        return $this->AuthnQueryServices;
    }


    /**
     * Set the AuthnQueryService endpoints
     *
     * @param \SimpleSAML\SAML2\XML\md\AbstractEndpointType[] $authnQueryServices
     * @throws \SimpleSAML\Assert\AssertionFailedException
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
     * @return \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    public function getAssertionIDRequestServices(): array
    {
        return $this->AssertionIDRequestServices;
    }


    /**
     * Set the AssertionIDRequestService endpoints
     *
     * @param \SimpleSAML\SAML2\XML\md\AbstractEndpointType[] $assertionIDRequestServices
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setAssertionIDRequestService(array $assertionIDRequestServices = []): void
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
     * @return \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    public function getNameIDFormats(): array
    {
        return $this->NameIDFormats;
    }


    /**
     * Set the values of the NameIDFormat
     *
     * @param \SimpleSAML\SAML2\XML\md\NameIDFormat[] $nameIDFormats
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    protected function setNameIDFormat(array $nameIDFormats): void
    {
        Assert::allIsInstanceOf($nameIDFormats, NameIDFormat::class);
        $this->NameIDFormats = $nameIDFormats;
    }


    /**
     * Add this IDPSSODescriptor to an EntityDescriptor.
     *
     * @param \DOMElement|null $parent The EntityDescriptor we should append this AuthnAuthorityDescriptor to.
     *
     * @return \DOMElement
     * @throws \Exception
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

        foreach ($this->NameIDFormats as $nidFormat) {
            $nidFormat->toXML($e);
        }

        if ($this->signer !== null) {
            $signedXML = $this->doSign($e);
            $signedXML->insertBefore($this->signature->toXML($signedXML), $signedXML->firstChild);
            return $signedXML;
        }

        return $e;
    }
}
