<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function preg_split;

/**
 * Class representing SAML 2 metadata AuthnAuthorityDescriptor.
 *
 * @package simplesamlphp/saml2
 */
final class AuthnAuthorityDescriptor extends AbstractRoleDescriptorType
{
    /**
     * AuthnAuthorityDescriptor constructor.
     *
     * @param array $authnQueryService
     * @param array $protocolSupportEnumeration
     * @param array $assertionIDRequestService
     * @param array $nameIDFormat
     * @param string|null $ID
     * @param \DateTimeImmutable|null $validUntil
     * @param string|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     * @param array $keyDescriptor
     * @param array $contact
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    public function __construct(
        protected array $authnQueryService,
        array $protocolSupportEnumeration,
        protected array $assertionIDRequestService = [],
        protected array $nameIDFormat = [],
        string $ID = null,
        ?DateTimeImmutable $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        ?Organization $organization = null,
        array $keyDescriptor = [],
        array $contact = [],
        array $namespacedAttributes = [],
    ) {
        Assert::maxCount($authnQueryService, C::UNBOUNDED_LIMIT);
        Assert::minCount($authnQueryService, 1, 'Missing at least one AuthnQueryService in AuthnAuthorityDescriptor.');
        Assert::allIsInstanceOf(
            $authnQueryService,
            AbstractEndpointType::class,
            'AuthnQueryService must be an instance of EndpointType',
        );
        Assert::maxCount($assertionIDRequestService, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf(
            $assertionIDRequestService,
            AbstractEndpointType::class,
            'AssertionIDRequestServices must be an instance of EndpointType',
        );
        Assert::maxCount($nameIDFormat, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($nameIDFormat, NameIDFormat::class);

        parent::__construct(
            $protocolSupportEnumeration,
            $ID,
            $validUntil,
            $cacheDuration,
            $extensions,
            $errorURL,
            $keyDescriptor,
            $organization,
            $contact,
            $namespacedAttributes,
        );
    }


    /**
     * Collect the AuthnQueryService endpoints
     *
     * @return \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    public function getAuthnQueryService(): array
    {
        return $this->authnQueryService;
    }


    /**
     * Collect the AssertionIDRequestService endpoints
     *
     * @return \SimpleSAML\SAML2\XML\md\AbstractEndpointType[]
     */
    public function getAssertionIDRequestService(): array
    {
        return $this->assertionIDRequestService;
    }


    /**
     * Collect the values of the NameIDFormat
     *
     * @return \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    public function getNameIDFormat(): array
    {
        return $this->nameIDFormat;
    }


    /**
     * Initialize an IDPSSODescriptor from an existing XML document.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AuthnAuthorityDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnAuthorityDescriptor::NS, InvalidDOMElementException::class);

        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');

        $authnQueryServices = AuthnQueryService::getChildrenOfClass($xml);
        $assertionIDRequestServices = AssertionIDRequestService::getChildrenOfClass($xml);
        $nameIDFormats = NameIDFormat::getChildrenOfClass($xml);

        $validUntil = self::getOptionalAttribute($xml, 'validUntil', null);
        SAMLAssert::nullOrValidDateTime($validUntil);

        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount(
            $orgs,
            1,
            'More than one Organization found in this descriptor',
            TooManyElementsException::class,
        );

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one md:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount(
            $signature,
            1,
            'Only one ds:Signature element is allowed.',
            TooManyElementsException::class,
        );

        $authority = new static(
            $authnQueryServices,
            preg_split('/[\s]+/', trim($protocols)),
            $assertionIDRequestServices,
            $nameIDFormats,
            self::getOptionalAttribute($xml, 'ID', null),
            $validUntil !== null ? new DateTimeImmutable($validUntil) : null,
            self::getOptionalAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getOptionalAttribute($xml, 'errorURL', null),
            !empty($orgs) ? $orgs[0] : null,
            KeyDescriptor::getChildrenOfClass($xml),
            ContactPerson::getChildrenOfClass($xml),
            self::getAttributesNSFromXML($xml),
        );

        if (!empty($signature)) {
            $authority->setSignature($signature[0]);
            $authority->setXML($xml);
        }

        return $authority;
    }


    /**
     * Add this IDPSSODescriptor to an EntityDescriptor.
     *
     * @param \DOMElement|null $parent The EntityDescriptor we should append this AuthnAuthorityDescriptor to.
     *
     * @return \DOMElement
     * @throws \Exception
     */
    public function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        foreach ($this->getAuthnQueryService() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getAssertionIDRequestService() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getNameIDFormat() as $nidFormat) {
            $nidFormat->toXML($e);
        }

        return $e;
    }
}
