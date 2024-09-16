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
 * Class representing SAML 2 metadata PDPDescriptor.
 *
 * @package simplesamlphp/saml2
 */
final class PDPDescriptor extends AbstractRoleDescriptorType
{
    /**
     * PDPDescriptor constructor.
     *
     * @param \SimpleSAML\SAML2\XML\md\AuthzService[] $authzService
     * @param string[] $protocolSupportEnumeration
     * @param \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[] $assertionIDRequestService
     * @param \SimpleSAML\SAML2\XML\md\NameIDFormat[] $nameIDFormat
     * @param string|null $ID
     * @param \DateTimeImmutable|null $validUntil
     * @param string|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptors
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contacts
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    public function __construct(
        protected array $authzService,
        array $protocolSupportEnumeration,
        protected array $assertionIDRequestService = [],
        protected array $nameIDFormat = [],
        ?string $ID = null,
        ?DateTimeImmutable $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        ?Organization $organization = null,
        array $keyDescriptors = [],
        array $contacts = [],
        array $namespacedAttributes = [],
    ) {
        Assert::maxCount($authzService, C::UNBOUNDED_LIMIT);
        Assert::minCount($authzService, 1, 'At least one md:AuthzService endpoint must be present.');
        Assert::allIsInstanceOf(
            $authzService,
            AuthzService::class,
            'All md:AuthzService endpoints must be an instance of AuthzService.',
        );
        Assert::maxCount($assertionIDRequestService, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf(
            $assertionIDRequestService,
            AssertionIDRequestService::class,
            'All md:AssertionIDRequestService endpoints must be an instance of AssertionIDRequestService.',
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
            $keyDescriptors,
            $organization,
            $contacts,
            $namespacedAttributes,
        );
    }


    /**
     * Get the AuthzService endpoints of this PDPDescriptor
     *
     * @return \SimpleSAML\SAML2\XML\md\AuthzService[]
     */
    public function getAuthzService(): array
    {
        return $this->authzService;
    }


    /**
     * Get the AssertionIDRequestService endpoints of this PDPDescriptor
     *
     * @return \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[]
     */
    public function getAssertionIDRequestService(): array
    {
        return $this->assertionIDRequestService;
    }


    /**
     * Get the NameIDFormats supported by this PDPDescriptor
     *
     * @return \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    public function getNameIDFormat(): array
    {
        return $this->nameIDFormat;
    }


    /**
     * Initialize an IDPSSODescriptor from a given XML document.
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
        Assert::same($xml->localName, 'PDPDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, PDPDescriptor::NS, InvalidDOMElementException::class);

        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');
        $validUntil = self::getOptionalAttribute($xml, 'validUntil', null);
        SAMLAssert::nullOrValidDateTime($validUntil);

        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount(
            $orgs,
            1,
            'More than one Organization found in this descriptor',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one md:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $pdp = new static(
            AuthzService::getChildrenOfClass($xml),
            preg_split('/[\s]+/', trim($protocols)),
            AssertionIDRequestService::getChildrenOfClass($xml),
            NameIDFormat::getChildrenOfClass($xml),
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
            $pdp->setSignature($signature[0]);
            $pdp->setXML($xml);
        }

        return $pdp;
    }


    /**
     * Add this PDPDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this IDPSSODescriptor to.
     * @return \DOMElement
     * @throws \Exception
     */
    public function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        foreach ($this->getAuthzService() as $ep) {
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
