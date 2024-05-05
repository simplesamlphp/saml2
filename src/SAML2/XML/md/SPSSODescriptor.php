<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_filter;
use function is_bool;
use function preg_split;

/**
 * Class representing SAML 2 SPSSODescriptor.
 *
 * @package simplesamlphp/saml2
 */
final class SPSSODescriptor extends AbstractSSODescriptor
{
    /**
     * SPSSODescriptor constructor.
     *
     * @param array<\SimpleSAML\SAML2\XML\md\AssertionConsumerService> $assertionConsumerService
     * @param string[] $protocolSupportEnumeration
     * @param bool|null $authnRequestsSigned
     * @param bool|null $wantAssertionsSigned
     * @param array<\SimpleSAML\SAML2\XML\md\AttributeConsumingService> $attributeConsumingService
     * @param string|null $ID
     * @param \DateTimeImmutable|null $validUntil
     * @param string|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param array<\SimpleSAML\SAML2\XML\md\KeyDescriptor> $keyDescriptors
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     * @param array<\SimpleSAML\SAML2\XML\md\ContactPerson> $contacts
     * @param array<\SimpleSAML\SAML2\XML\md\ArtifactResolutionService> $artifactResolutionService
     * @param array<\SimpleSAML\SAML2\XML\md\SingleLogoutService> $singleLogoutService
     * @param array<\SimpleSAML\SAML2\XML\md\ManageNameIDService> $manageNameIDService
     * @param array<\SimpleSAML\SAML2\XML\md\NameIDFormat> $nameIDFormat
     */
    public function __construct(
        protected array $assertionConsumerService,
        array $protocolSupportEnumeration,
        protected ?bool $authnRequestsSigned = null,
        protected ?bool $wantAssertionsSigned = null,
        protected array $attributeConsumingService = [],
        ?string $ID = null,
        ?DateTimeImmutable $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        array $keyDescriptors = [],
        ?Organization $organization = null,
        array $contacts = [],
        array $artifactResolutionService = [],
        array $singleLogoutService = [],
        array $manageNameIDService = [],
        array $nameIDFormat = [],
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
            $contacts,
            $artifactResolutionService,
            $singleLogoutService,
            $manageNameIDService,
            $nameIDFormat
        );

        Assert::maxCount($assertionConsumerService, C::UNBOUNDED_LIMIT);
        Assert::minCount($assertionConsumerService, 1, 'At least one AssertionConsumerService must be specified.');
        Assert::allIsInstanceOf(
            $assertionConsumerService,
            AssertionConsumerService::class,
            'All md:AssertionConsumerService endpoints must be an instance of AssertionConsumerService.',
        );
        Assert::maxCount($attributeConsumingService, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf(
            $attributeConsumingService,
            AttributeConsumingService::class,
            'All md:AttributeConsumingService endpoints must be an instance of AttributeConsumingService.',
        );

        // test that only one ACS is marked as default
        Assert::maxCount(
            array_filter(
                $attributeConsumingService,
                function (AttributeConsumingService $acs) {
                    return $acs->getIsDefault() === true;
                }
            ),
            1,
            'Only one md:AttributeConsumingService can be set as default.',
        );
    }


    /**
     * Collect the value of the AuthnRequestsSigned-property
     *
     * @return bool|null
     */
    public function getAuthnRequestsSigned(): ?bool
    {
        return $this->authnRequestsSigned;
    }


    /**
     * Collect the value of the WantAssertionsSigned-property
     *
     * @return bool|null
     */
    public function getWantAssertionsSigned(): ?bool
    {
        return $this->wantAssertionsSigned;
    }


    /**
     * Collect the value of the AssertionConsumerService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AssertionConsumerService[]
     */
    public function getAssertionConsumerService(): array
    {
        return $this->assertionConsumerService;
    }


    /**
     * Collect the value of the AttributeConsumingService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AttributeConsumingService[]
     */
    public function getAttributeConsumingService(): array
    {
        return $this->attributeConsumingService;
    }


    /**
     * Convert XML into a SPSSODescriptor
     *
     * @param \DOMElement $xml The XML element we should load
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
        Assert::same($xml->localName, 'SPSSODescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SPSSODescriptor::NS, InvalidDOMElementException::class);

        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');
        $validUntil = self::getOptionalAttribute($xml, 'validUntil', null);
        Assert::nullOrValidDateTimeZulu($validUntil);

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

        $spssod = new static(
            AssertionConsumerService::getChildrenOfClass($xml),
            preg_split('/[\s]+/', trim($protocols)),
            self::getOptionalBooleanAttribute($xml, 'AuthnRequestsSigned', null),
            self::getOptionalBooleanAttribute($xml, 'WantAssertionsSigned', null),
            AttributeConsumingService::getChildrenOfClass($xml),
            self::getOptionalAttribute($xml, 'ID', null),
            $validUntil !== null ? new DateTimeImmutable($validUntil) : null,
            self::getOptionalAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getOptionalAttribute($xml, 'errorURL', null),
            KeyDescriptor::getChildrenOfClass($xml),
            !empty($orgs) ? $orgs[0] : null,
            ContactPerson::getChildrenOfClass($xml),
            ArtifactResolutionService::getChildrenOfClass($xml),
            SingleLogoutService::getChildrenOfClass($xml),
            ManageNameIDService::getChildrenOfClass($xml),
            NameIDFormat::getChildrenOfClass($xml),
        );

        if (!empty($signature)) {
            $spssod->setSignature($signature[0]);
            $spssod->setXML($xml);
        }

        return $spssod;
    }


    /**
     * Convert this assertion to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    public function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        if (is_bool($this->getAuthnRequestsSigned())) {
            $e->setAttribute('AuthnRequestsSigned', $this->getAuthnRequestsSigned() ? 'true' : 'false');
        }

        if (is_bool($this->getWantAssertionsSigned())) {
            $e->setAttribute('WantAssertionsSigned', $this->getWantAssertionsSigned() ? 'true' : 'false');
        }

        foreach ($this->getAssertionConsumerService() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getAttributeConsumingService() as $acs) {
            $acs->toXML($e);
        }

        return $e;
    }
}
