<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\ds\Signature;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class representing SAML 2 metadata AttributeAuthorityDescriptor.
 *
 * @package simplesamlphp/saml2
 */
final class AttributeAuthorityDescriptor extends AbstractRoleDescriptor
{
    /**
     * List of AttributeService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\AttributeService[]
     */
    protected $AttributeServices = [];

    /**
     * List of AssertionIDRequestService endpoints.
     *
     * Array with EndpointType objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[]
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
     * List of supported attribute profiles.
     *
     * Array with strings.
     *
     * @var array
     */
    protected $AttributeProfiles = [];

    /**
     * List of supported attributes.
     *
     * Array with \SimpleSAML\SAML2\XML\saml\Attribute objects.
     *
     * @var Attribute[]
     */
    protected $Attributes = [];


    /**
     * AttributeAuthorityDescriptor constructor.
     *
     * @param \SimpleSAML\SAML2\XML\md\AttributeService[] $attributeServices
     * @param string[] $protocolSupportEnumeration
     * @param \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[]|null $assertionIDRequestService
     * @param string[]|null $nameIDFormats
     * @param string[]|null $attributeProfiles
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[]|null $attributes
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param string|null $errorURL
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptors
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contacts
     */
    public function __construct(
        array $attributeServices,
        array $protocolSupportEnumeration,
        ?array $assertionIDRequestService = [],
        ?array $nameIDFormats = [],
        ?array $attributeProfiles = [],
        ?array $attributes = [],
        ?string $ID = null,
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
        $this->setAttributeServices($attributeServices);
        $this->setAssertionIDRequestServices($assertionIDRequestService);
        $this->setNameIDFormats($nameIDFormats);
        $this->setAttributeProfiles($attributeProfiles);
        $this->setAttributes($attributes);
    }


    /**
     * Collect the value of the AttributeService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AttributeService[]
     */
    public function getAttributeServices(): array
    {
        return $this->AttributeServices;
    }


    /**
     * Set the value of the AttributeService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AttributeService[] $attributeServices
     * @return void
     */
    protected function setAttributeServices(array $attributeServices): void
    {
        Assert::minCount(
            $attributeServices,
            1,
            'AttributeAuthorityDescriptor must contain at least one AttributeService.'
        );
        Assert::allIsInstanceOf(
            $attributeServices,
            AttributeService::class,
            'AttributeService is not an instance of EndpointType.'
        );
        $this->AttributeServices = $attributeServices;
    }


    /**
     * Collect the value of the NameIDFormat-property
     *
     * @return string[]
     */
    public function getNameIDFormats(): array
    {
        return $this->NameIDFormats;
    }


    /**
     * Set the value of the NameIDFormat-property
     *
     * @param string[]|null $nameIDFormats
     * @return void
     */
    protected function setNameIDFormats(?array $nameIDFormats): void
    {
        if ($nameIDFormats === null) {
            return;
        }
        Assert::allStringNotEmpty($nameIDFormats, 'NameIDFormat cannot be an empty string.');
        $this->NameIDFormats = $nameIDFormats;
    }


    /**
     * Collect the value of the AssertionIDRequestService-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[]
     */
    public function getAssertionIDRequestServices(): array
    {
        return $this->AssertionIDRequestServices;
    }


    /**
     * Set the value of the AssertionIDRequestService-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AssertionIDRequestService[] $assertionIDRequestServices
     * @return void
     */
    protected function setAssertionIDRequestServices(?array $assertionIDRequestServices): void
    {
        if ($assertionIDRequestServices === null) {
            return;
        }

        Assert::allIsInstanceOf($assertionIDRequestServices, AssertionIDRequestService::class);
        $this->AssertionIDRequestServices = $assertionIDRequestServices;
    }


    /**
     * Collect the value of the AttributeProfile-property
     *
     * @return string[]
     */
    public function getAttributeProfiles(): array
    {
        return $this->AttributeProfiles;
    }


    /**
     * Set the value of the AttributeProfile-property
     *
     * @param string[]|null $attributeProfiles
     * @return void
     */
    protected function setAttributeProfiles(?array $attributeProfiles): void
    {
        if ($attributeProfiles === null) {
            return;
        }
        Assert::allStringNotEmpty($attributeProfiles, 'AttributeProfile cannot be an empty string.');
        $this->AttributeProfiles = $attributeProfiles;
    }


    /**
     * Collect the value of the Attribute-property
     *
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->Attributes;
    }


    /**
     * Set the value of the Attribute-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\Attribute[]|null $attributes
     */
    protected function setAttributes(?array $attributes): void
    {
        if ($attributes === null) {
            return;
        }
        Assert::allIsInstanceOf($attributes, Attribute::class);
        $this->Attributes = $attributes;
    }


    /**
     * Initialize an IDPSSODescriptor.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\MissingElementException if one of the mandatory child-elements is missing
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AttributeAuthorityDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AttributeAuthorityDescriptor::NS, InvalidDOMElementException::class);

        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');

        $attrServices = AttributeService::getChildrenOfClass($xml);
        Assert::notEmpty(
            $attrServices,
            'Must have at least one AttributeService in AttributeAuthorityDescriptor.',
            MissingElementException::class
        );

        $assertIDReqServices = AssertionIDRequestService::getChildrenOfClass($xml);
        $nameIDFormats = XMLUtils::extractStrings($xml, Constants::NS_MD, 'NameIDFormat');
        $attrProfiles = XMLUtils::extractStrings($xml, Constants::NS_MD, 'AttributeProfile');

        $attributes = Attribute::getChildrenOfClass($xml);
        $validUntil = self::getAttribute($xml, 'validUntil', null);

        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor', TooManyElementsException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.', TooManyElementsException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $authority = new self(
            $attrServices,
            preg_split('/[\s]+/', trim($protocols)),
            $assertIDReqServices,
            $nameIDFormats,
            $attrProfiles,
            $attributes,
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
     * Add this AttributeAuthorityDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement|null $parent The EntityDescriptor we should append this IDPSSODescriptor to.
     * @return \DOMElement
     * @throws \Exception
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        foreach ($this->AttributeServices as $ep) {
            $ep->toXML($e);
        }

        foreach ($this->AssertionIDRequestServices as $ep) {
            $ep->toXML($e);
        }

        XMLUtils::addStrings($e, Constants::NS_MD, 'md:NameIDFormat', false, $this->NameIDFormats);
        XMLUtils::addStrings($e, Constants::NS_MD, 'md:AttributeProfile', false, $this->AttributeProfiles);

        foreach ($this->Attributes as $a) {
            $a->toXML($e);
        }

        return $this->signElement($e);
    }
}
