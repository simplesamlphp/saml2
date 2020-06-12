<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\ds\Signature;
use SimpleSAML\Assert\Assert;

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
    protected $authnRequestsSigned = null;

    /**
     * Whether this SP wants the Assertion elements to be signed.
     *
     * @var bool|null
     */
    protected $wantAssertionsSigned = null;

    /**
     * List of AssertionConsumerService endpoints for this SP.
     *
     * Array with IndexedEndpointType objects.
     *
     * @var \SAML2\XML\md\AssertionConsumerService[]
     */
    protected $assertionConsumerService = [];

    /**
     * List of AttributeConsumingService descriptors for this SP.
     *
     * Array with \SAML2\XML\md\AttributeConsumingService objects.
     *
     * @var \SAML2\XML\md\AttributeConsumingService[]
     */
    protected $attributeConsumingService = [];



    /**
     * SPSSODescriptor constructor.
     *
     * @param \SAML2\XML\md\AssertionConsumerService[] $assertionConsumerService
     * @param string[] $protocolSupportEnumeration
     * @param bool|null $authnRequestsSigned
     * @param bool|null $wantAssertionsSigned
     * @param \SAML2\XML\md\AttributeConsumingService[] $attributeConsumingService
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SAML2\XML\md\KeyDescriptor[] $keyDescriptors
     * @param \SAML2\XML\md\Organization|null $organization
     * @param \SAML2\XML\md\ContactPerson[] $contacts
     * @param \SAML2\XML\md\ArtifactResolutionService[] $artifactResolutionService
     * @param \SAML2\XML\md\SingleLogoutService[] $singleLogoutService
     * @param \SAML2\XML\md\ManageNameIDService[] $manageNameIDService
     * @param string[] $nameIDFormat
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
     * @return void
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
     * @return void
     */
    private function setWantAssertionsSigned(?bool $flag): void
    {
        $this->wantAssertionsSigned = $flag;
    }


    /**
     * Collect the value of the AssertionConsumerService-property
     *
     * @return \SAML2\XML\md\AssertionConsumerService[]
     */
    public function getAssertionConsumerService(): array
    {
        return $this->assertionConsumerService;
    }


    /**
     * Set the value of the AssertionConsumerService-property
     *
     * @param \SAML2\XML\md\AssertionConsumerService[] $acs
     * @return void
     * @throws \InvalidArgumentException
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
     * @return \SAML2\XML\md\AttributeConsumingService[]
     */
    public function getAttributeConsumingService(): array
    {
        return $this->attributeConsumingService;
    }


    /**
     * Set the value of the AttributeConsumingService-property
     *
     * @param \SAML2\XML\md\AttributeConsumingService[] $acs
     * @return void
     * @throws \InvalidArgumentException
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
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong*@throws \Exception
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        /** @var string $protocols */
        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');

        Assert::same($xml->localName, 'SPSSODescriptor');
        Assert::same($xml->namespaceURI, SPSSODescriptor::NS);

        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor');

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.');

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.');

        $spssod = new self(
            AssertionConsumerService::getChildrenOfClass($xml),
            preg_split('/[\s]+/', trim($protocols)),
            self::getBooleanAttribute($xml, 'AuthnRequestsSigned', null),
            self::getBooleanAttribute($xml, 'WantAssertionsSigned', null),
            AttributeConsumingService::getChildrenOfClass($xml),
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? Utils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getAttribute($xml, 'errorURL', null),
            KeyDescriptor::getChildrenOfClass($xml),
            !empty($orgs) ? $orgs[0] : null,
            ContactPerson::getChildrenOfClass($xml),
            ArtifactResolutionService::getChildrenOfClass($xml),
            SingleLogoutService::getChildrenOfClass($xml),
            ManageNameIDService::getChildrenOfClass($xml),
            Utils::extractStrings($xml, Constants::NS_MD, 'NameIDFormat')
        );
        if (!empty($signature)) {
            $spssod->setSignature($signature[0]);
        }
        return $spssod;
    }


    /**
     * Add this SPSSODescriptor to an EntityDescriptor.
     *
     * @param \DOMElement|null $parent The EntityDescriptor we should append this SPSSODescriptor to.
     * @return \DOMElement
     * @throws \Exception
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        if (is_bool($this->authnRequestsSigned)) {
            $e->setAttribute('AuthnRequestsSigned', $this->authnRequestsSigned ? 'true' : 'false');
        }

        if (is_bool($this->wantAssertionsSigned)) {
            $e->setAttribute('WantAssertionsSigned', $this->wantAssertionsSigned ? 'true' : 'false');
        }

        foreach ($this->assertionConsumerService as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->attributeConsumingService as $acs) {
            $acs->toXML($e);
        }

        return $this->signElement($e);
    }
}
