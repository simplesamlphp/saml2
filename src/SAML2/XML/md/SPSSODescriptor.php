<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
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
     * Whether this SP signs authentication requests.
     *
     * @var bool|null
     */
    protected ?bool $authnRequestsSigned = null;

    /**
     * Whether this SP wants the Assertion elements to be signed.
     *
     * @var bool|null
     */
    protected ?bool $wantAssertionsSigned = null;

    /**
     * List of AssertionConsumerService endpoints for this SP.
     *
     * Array with IndexedEndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\AssertionConsumerService[]
     */
    protected array $assertionConsumerService = [];

    /**
     * List of AttributeConsumingService descriptors for this SP.
     *
     * Array with \SimpleSAML\SAML2\XML\md\AttributeConsumingService objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\AttributeConsumingService[]
     */
    protected array $attributeConsumingService = [];



    /**
     * SPSSODescriptor constructor.
     *
     * @param \SimpleSAML\SAML2\XML\md\AssertionConsumerService[] $assertionConsumerService
     * @param string[] $protocolSupportEnumeration
     * @param bool|null $authnRequestsSigned
     * @param bool|null $wantAssertionsSigned
     * @param \SimpleSAML\SAML2\XML\md\AttributeConsumingService[] $attributeConsumingService
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptors
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contacts
     * @param \SimpleSAML\SAML2\XML\md\ArtifactResolutionService[] $artifactResolutionService
     * @param \SimpleSAML\SAML2\XML\md\SingleLogoutService[] $singleLogoutService
     * @param \SimpleSAML\SAML2\XML\md\ManageNameIDService[] $manageNameIDService
     * @param \SimpleSAML\SAML2\XML\md\NameIDFormat[] $nameIDFormat
     */
    public function __construct(
        array $assertionConsumerService,
        array $protocolSupportEnumeration,
        ?bool $authnRequestsSigned = null,
        ?bool $wantAssertionsSigned = null,
        array $attributeConsumingService = [],
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        array $keyDescriptors = [],
        ?Organization $organization = null,
        array $contacts = [],
        array $artifactResolutionService = [],
        array $singleLogoutService = [],
        array $manageNameIDService = [],
        array $nameIDFormat = []
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

        $this->setAssertionConsumerService($assertionConsumerService);
        $this->setAuthnRequestsSigned($authnRequestsSigned);
        $this->setWantAssertionsSigned($wantAssertionsSigned);
        $this->setAttributeConsumingService($attributeConsumingService);

        // test that only one ACS is marked as default
        Assert::maxCount(
            array_filter(
                $this->getAttributeConsumingService(),
                function (AttributeConsumingService $acs) {
                    return $acs->getIsDefault() === true;
                }
            ),
            1,
            'Only one md:AttributeConsumingService can be set as default.'
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
     * Set the value of the AuthnRequestsSigned-property
     *
     * @param bool|null $flag
     */
    private function setAuthnRequestsSigned(?bool $flag): void
    {
        $this->authnRequestsSigned = $flag;
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
     * Set the value of the WantAssertionsSigned-property
     *
     * @param bool|null $flag
     */
    private function setWantAssertionsSigned(?bool $flag): void
    {
        $this->wantAssertionsSigned = $flag;
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
     * Set the value of the AssertionConsumerService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AssertionConsumerService[] $acs
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    private function setAssertionConsumerService(array $acs): void
    {
        Assert::minCount($acs, 1, 'At least one AssertionConsumerService must be specified.');
        Assert::allIsInstanceOf(
            $acs,
            AssertionConsumerService::class,
            'All md:AssertionConsumerService endpoints must be an instance of AssertionConsumerService.'
        );
        $this->assertionConsumerService = $acs;
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
     * Set the value of the AttributeConsumingService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AttributeConsumingService[] $acs
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    private function setAttributeConsumingService(array $acs): void
    {
        Assert::allIsInstanceOf(
            $acs,
            AttributeConsumingService::class,
            'All md:AttributeConsumingService endpoints must be an instance of AttributeConsumingService.'
        );
        $this->attributeConsumingService = $acs;
    }


    /**
     * Convert XML into a SPSSODescriptor
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'SPSSODescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SPSSODescriptor::NS, InvalidDOMElementException::class);

        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');
        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor', TooManyElementsException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.', TooManyElementsException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $spssod = new static(
            AssertionConsumerService::getChildrenOfClass($xml),
            preg_split('/[\s]+/', trim($protocols)),
            self::getBooleanAttribute($xml, 'AuthnRequestsSigned', null),
            self::getBooleanAttribute($xml, 'WantAssertionsSigned', null),
            AttributeConsumingService::getChildrenOfClass($xml),
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? XMLUtils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getAttribute($xml, 'errorURL', null),
            KeyDescriptor::getChildrenOfClass($xml),
            !empty($orgs) ? $orgs[0] : null,
            ContactPerson::getChildrenOfClass($xml),
            ArtifactResolutionService::getChildrenOfClass($xml),
            SingleLogoutService::getChildrenOfClass($xml),
            ManageNameIDService::getChildrenOfClass($xml),
            NameIDFormat::getChildrenOfClass($xml)
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
