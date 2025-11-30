<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIListValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\BooleanValue;
use SimpleSAML\XMLSchema\Type\DurationValue;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function var_export;

/**
 * Class representing SAML 2 IDPSSODescriptor.
 *
 * @package simplesamlphp/saml2
 */
final class IDPSSODescriptor extends AbstractSSODescriptor implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * IDPSSODescriptor constructor.
     *
     * @param \SimpleSAML\SAML2\XML\md\SingleSignOnService[] $singleSignOnService
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIListValue $protocolSupportEnumeration
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue|null $wantAuthnRequestsSigned
     * @param \SimpleSAML\SAML2\XML\md\NameIDMappingService[] $nameIDMappingService
     * @param \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[] $assertionIDRequestService
     * @param \SimpleSAML\SAML2\XML\md\AttributeProfile[] $attributeProfile
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[] $attribute
     * @param \SimpleSAML\XMLSchema\Type\IDValue|null $ID
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $validUntil
     * @param \SimpleSAML\XMLSchema\Type\DurationValue|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $errorURL
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptor
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contact
     * @param \SimpleSAML\SAML2\XML\md\ArtifactResolutionService[] $artifactResolutionService
     * @param \SimpleSAML\SAML2\XML\md\SingleLogoutService[] $singleLogoutService
     * @param \SimpleSAML\SAML2\XML\md\ManageNameIDService[] $manageNameIDService
     * @param \SimpleSAML\SAML2\XML\md\NameIDFormat[] $nameIDFormat
     */
    public function __construct(
        protected array $singleSignOnService,
        SAMLAnyURIListValue $protocolSupportEnumeration,
        protected ?BooleanValue $wantAuthnRequestsSigned = null,
        protected array $nameIDMappingService = [],
        protected array $assertionIDRequestService = [],
        protected array $attributeProfile = [],
        protected array $attribute = [],
        ?IDValue $ID = null,
        ?SAMLDateTimeValue $validUntil = null,
        ?DurationValue $cacheDuration = null,
        ?Extensions $extensions = null,
        ?SAMLAnyURIValue $errorURL = null,
        array $keyDescriptor = [],
        ?Organization $organization = null,
        array $contact = [],
        array $artifactResolutionService = [],
        array $singleLogoutService = [],
        array $manageNameIDService = [],
        array $nameIDFormat = [],
    ) {
        Assert::maxCount($singleSignOnService, C::UNBOUNDED_LIMIT);
        Assert::minCount($singleSignOnService, 1, 'At least one SingleSignOnService must be specified.');
        Assert::allIsInstanceOf(
            $singleSignOnService,
            SingleSignOnService::class,
            'All md:SingleSignOnService endpoints must be an instance of SingleSignOnService.',
        );
        Assert::maxCount($nameIDMappingService, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf(
            $nameIDMappingService,
            NameIDMappingService::class,
            'All md:NameIDMappingService endpoints must be an instance of NameIDMappingService.',
        );
        Assert::maxCount($assertionIDRequestService, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf(
            $assertionIDRequestService,
            AssertionIDRequestService::class,
            'All md:AssertionIDRequestService endpoints must be an instance of AssertionIDRequestService.',
        );
        Assert::maxCount($attributeProfile, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($attributeProfile, AttributeProfile::class);
        Assert::maxCount($attribute, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf(
            $attribute,
            Attribute::class,
            'All md:Attribute elements must be an instance of Attribute.',
        );

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
            $artifactResolutionService,
            $singleLogoutService,
            $manageNameIDService,
            $nameIDFormat,
        );
    }


    /**
     * Collect the value of the WantAuthnRequestsSigned-property
     *
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null
     */
    public function wantAuthnRequestsSigned(): ?BooleanValue
    {
        return $this->wantAuthnRequestsSigned;
    }


    /**
     * Get the SingleSignOnService endpoints
     *
     * @return \SimpleSAML\SAML2\XML\md\SingleSignOnService[]
     */
    public function getSingleSignOnService(): array
    {
        return $this->singleSignOnService;
    }


    /**
     * Get the NameIDMappingService endpoints
     *
     * @return \SimpleSAML\SAML2\XML\md\NameIDMappingService[]
     */
    public function getNameIDMappingService(): array
    {
        return $this->nameIDMappingService;
    }


    /**
     * Collect the AssertionIDRequestService endpoints
     *
     * @return \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[]
     */
    public function getAssertionIDRequestService(): array
    {
        return $this->assertionIDRequestService;
    }


    /**
     * Get the attribute profiles supported
     *
     * @return \SimpleSAML\SAML2\XML\md\AttributeProfile[]
     */
    public function getAttributeProfile(): array
    {
        return $this->attributeProfile;
    }


    /**
     * Get the attributes supported by this IdP
     *
     * @return \SimpleSAML\SAML2\XML\saml\Attribute[]
     */
    public function getSupportedAttribute(): array
    {
        return $this->attribute;
    }


    /**
     * Initialize an IDPSSODescriptor.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'IDPSSODescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, IDPSSODescriptor::NS, InvalidDOMElementException::class);

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

        $idpssod = new static(
            SingleSignOnService::getChildrenOfClass($xml),
            self::getAttribute($xml, 'protocolSupportEnumeration', SAMLAnyURIListValue::class),
            self::getOptionalAttribute($xml, 'WantAuthnRequestsSigned', BooleanValue::class, null),
            NameIDMappingService::getChildrenOfClass($xml),
            AssertionIDRequestService::getChildrenOfClass($xml),
            AttributeProfile::getChildrenOfClass($xml),
            Attribute::getChildrenOfClass($xml),
            self::getOptionalAttribute($xml, 'ID', IDValue::class, null),
            self::getOptionalAttribute($xml, 'validUntil', SAMLDateTimeValue::class, null),
            self::getOptionalAttribute($xml, 'cacheDuration', DurationValue::class, null),
            !empty($extensions) ? $extensions[0] : null,
            self::getOptionalAttribute($xml, 'errorURL', SAMLAnyURIValue::class, null),
            KeyDescriptor::getChildrenOfClass($xml),
            !empty($orgs) ? $orgs[0] : null,
            ContactPerson::getChildrenOfClass($xml),
            ArtifactResolutionService::getChildrenOfClass($xml),
            SingleLogoutService::getChildrenOfClass($xml),
            ManageNameIDService::getChildrenOfClass($xml),
            NameIDFormat::getChildrenOfClass($xml),
        );

        if (!empty($signature)) {
            $idpssod->setSignature($signature[0]);
            $idpssod->setXML($xml);
        }
        return $idpssod;
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

        if ($this->wantAuthnRequestsSigned() !== null) {
            $e->setAttribute(
                'WantAuthnRequestsSigned',
                var_export($this->wantAuthnRequestsSigned()->toBoolean(), true),
            );
        }

        foreach ($this->getSingleSignOnService() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getNameIDMappingService() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getAssertionIDRequestService() as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->getAttributeProfile() as $ap) {
            $ap->toXML($e);
        }

        foreach ($this->getSupportedAttribute() as $a) {
            $a->toXML($e);
        }

        return $e;
    }
}
