<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function preg_split;

/**
 * Class representing SAML 2 metadata AttributeAuthorityDescriptor.
 *
 * @package simplesamlphp/saml2
 */
final class AttributeAuthorityDescriptor extends AbstractRoleDescriptorType
{
    /**
     * AttributeAuthorityDescriptor constructor.
     *
     * @param \SimpleSAML\SAML2\XML\md\AttributeService[] $attributeService
     * @param string[] $protocolSupportEnumeration
     * @param \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[] $asssertionIDRequestService
     * @param \SimpleSAML\SAML2\XML\md\NameIDFormat[] $nameIDFormat
     * @param \SimpleSAML\SAML2\XML\md\AttributeProfile[] $attributeProfile
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[] $attribute
     * @param string|null $ID
     * @param \DateTimeImmutable|null $validUntil
     * @param string|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptor
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contact
     */
    public function __construct(
        protected array $attributeService,
        array $protocolSupportEnumeration,
        protected array $assertionIDRequestService = [],
        protected array $nameIDFormat = [],
        protected array $attributeProfile = [],
        protected array $attribute = [],
        ?string $ID = null,
        ?DateTimeImmutable $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        ?Organization $organization = null,
        array $keyDescriptor = [],
        array $contact = [],
    ) {
        Assert::maxCount($attributeService, C::UNBOUNDED_LIMIT);
        Assert::minCount(
            $attributeService,
            1,
            'AttributeAuthorityDescriptor must contain at least one AttributeService.',
            MissingElementException::class,
        );
        Assert::allIsInstanceOf(
            $attributeService,
            AttributeService::class,
            'AttributeService is not an instance of EndpointType.',
            InvalidDOMElementException::class,
        );
        Assert::maxCount($nameIDFormat, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($nameIDFormat, NameIDFormat::class);
        Assert::maxCount($assertionIDRequestService, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($assertionIDRequestService, AssertionIDRequestService::class);
        Assert::maxCount($attributeProfile, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($attributeProfile, AttributeProfile::class);
        Assert::maxCount($attribute, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($attribute, Attribute::class);

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
        );
    }


    /**
     * Collect the value of the AttributeService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AttributeService[]
     */
    public function getAttributeService(): array
    {
        return $this->attributeService;
    }


    /**
     * Collect the value of the NameIDFormat-property
     *
     * @return \SimpleSAML\SAML2\XML\md\NameIDFormat[]
     */
    public function getNameIDFormat(): array
    {
        return $this->nameIDFormat;
    }


    /**
     * Collect the value of the AssertionIDRequestService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[]
     */
    public function getAssertionIDRequestService(): array
    {
        return $this->assertionIDRequestService;
    }


    /**
     * Collect the value of the AttributeProfile-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AttributeProfile[]
     */
    public function getAttributeProfile(): array
    {
        return $this->attributeProfile;
    }


    /**
     * Collect the value of the Attribute-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attribute;
    }


    /**
     * Initialize an IDPSSODescriptor.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AttributeAuthorityDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeAuthorityDescriptor::NS, InvalidDOMElementException::class);

        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');
        $validUntil = self::getOptionalAttribute($xml, 'validUntil', null);
        Assert::nullOrValidDateTimeZulu($validUntil);

        $attrServices = AttributeService::getChildrenOfClass($xml);
        Assert::notEmpty(
            $attrServices,
            'Must have at least one AttributeService in AttributeAuthorityDescriptor.',
            MissingElementException::class,
        );

        $assertIDReqServices = AssertionIDRequestService::getChildrenOfClass($xml);
        $nameIDFormats = NameIDFormat::getChildrenOfClass($xml);
        $attrProfiles = AttributeProfile::getChildrenOfClass($xml);
        $attributes = Attribute::getChildrenOfClass($xml);

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
            $attrServices,
            preg_split('/[\s]+/', trim($protocols)),
            $assertIDReqServices,
            $nameIDFormats,
            $attrProfiles,
            $attributes,
            self::getOptionalAttribute($xml, 'ID', null),
            $validUntil !== null ? new DateTimeImmutable($validUntil) : null,
            self::getOptionalAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getOptionalAttribute($xml, 'errorURL', null),
            !empty($orgs) ? $orgs[0] : null,
            KeyDescriptor::getChildrenOfClass($xml),
            ContactPerson::getChildrenOfClass($xml),
        );

        if (!empty($signature)) {
            $authority->setSignature($signature[0]);
            $authority->setXML($xml);
        }
        return $authority;
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

        foreach ($this->getAttributeService() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getAssertionIDRequestService() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getNameIDFormat() as $nidFormat) {
            $nidFormat->toXML($e);
        }

        foreach ($this->getAttributeProfile() as $ap) {
            $ap->toXML($e);
        }

        foreach ($this->getAttributes() as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
