<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\AnyURIListValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\BooleanValue;
use SimpleSAML\XMLSchema\Type\DurationValue;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_filter;
use function var_export;

/**
 * Class representing SAML 2 SPSSODescriptor.
 *
 * @package simplesamlphp/saml2
 */
final class SPSSODescriptor extends AbstractSSODescriptor implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * SPSSODescriptor constructor.
     *
     * @param array<\SimpleSAML\SAML2\XML\md\AssertionConsumerService> $assertionConsumerService
     * @param \SimpleSAML\SAML2\Type\AnyURIListValue $protocolSupportEnumeration
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue|null $authnRequestsSigned
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue|null $wantAssertionsSigned
     * @param array<\SimpleSAML\SAML2\XML\md\AttributeConsumingService> $attributeConsumingService
     * @param \SimpleSAML\XMLSchema\Type\IDValue|null $ID
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $validUntil
     * @param \SimpleSAML\XMLSchema\Type\DurationValue|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $errorURL
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
        AnyURIListValue $protocolSupportEnumeration,
        protected ?BooleanValue $authnRequestsSigned = null,
        protected ?BooleanValue $wantAssertionsSigned = null,
        protected array $attributeConsumingService = [],
        ?IDValue $ID = null,
        ?SAMLDateTimeValue $validUntil = null,
        ?DurationValue $cacheDuration = null,
        ?Extensions $extensions = null,
        ?SAMLAnyURIValue $errorURL = null,
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
            $nameIDFormat,
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

        /**
         * E87:  test that only one ACS is marked as default
         */
        Assert::maxCount(
            array_filter(
                $attributeConsumingService,
                function (AttributeConsumingService $acs) {
                    return $acs->getIsDefault()?->toBoolean() === true;
                },
            ),
            1,
            'At most one <AttributeConsumingService> element can have the attribute isDefault set to true.',
        );
    }


    /**
     * Collect the value of the AuthnRequestsSigned-property
     *
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null
     */
    public function getAuthnRequestsSigned(): ?BooleanValue
    {
        return $this->authnRequestsSigned;
    }


    /**
     * Collect the value of the WantAssertionsSigned-property
     *
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null
     */
    public function getWantAssertionsSigned(): ?BooleanValue
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
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'SPSSODescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SPSSODescriptor::NS, InvalidDOMElementException::class);

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
            self::getAttribute($xml, 'protocolSupportEnumeration', AnyURIListValue::class),
            self::getOptionalAttribute($xml, 'AuthnRequestsSigned', BooleanValue::class, null),
            self::getOptionalAttribute($xml, 'WantAssertionsSigned', BooleanValue::class, null),
            AttributeConsumingService::getChildrenOfClass($xml),
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

        if ($this->getAuthnRequestsSigned() !== null) {
            $e->setAttribute('AuthnRequestsSigned', var_export($this->getAuthnRequestsSigned()->toBoolean(), true));
        }

        if ($this->getWantAssertionsSigned() !== null) {
            $e->setAttribute('WantAssertionsSigned', var_export($this->getWantAssertionsSigned()->toBoolean(), true));
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
