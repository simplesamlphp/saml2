<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\ExtendableElementTrait;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XML\XsNamespace as NS;

use function array_change_key_case;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_merge;

/**
 * Class representing SAML 2 Organization element.
 *
 * @package simplesamlphp/saml2
 */
final class Organization extends AbstractMdElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use ExtendableAttributesTrait;
    use ExtendableElementTrait;
    use SchemaValidatableElementTrait;


    /** The namespace-attribute for the xs:anyAttribute element */
    public const XS_ANY_ATTR_NAMESPACE = NS::OTHER;


    /**
     * Organization constructor.
     *
     * @param \SimpleSAML\SAML2\XML\md\OrganizationName[] $organizationName
     * @param \SimpleSAML\SAML2\XML\md\OrganizationDisplayName[] $organizationDisplayName
     * @param \SimpleSAML\SAML2\XML\md\OrganizationURL[] $organizationURL
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    public function __construct(
        protected array $organizationName,
        protected array $organizationDisplayName,
        protected array $organizationURL,
        ?Extensions $extensions = null,
        array $namespacedAttributes = [],
    ) {
        Assert::maxCount($organizationName, C::UNBOUNDED_LIMIT);
        Assert::maxCount($organizationDisplayName, C::UNBOUNDED_LIMIT);
        Assert::maxCount($organizationURL, C::UNBOUNDED_LIMIT);

        // [One or More]
        Assert::minCount($organizationName, 1, ProtocolViolationException::class);
        Assert::minCount($organizationDisplayName, 1, ProtocolViolationException::class);
        Assert::minCount($organizationURL, 1, ProtocolViolationException::class);

        Assert::allIsInstanceOf($organizationName, OrganizationName::class);
        Assert::allIsInstanceOf($organizationDisplayName, OrganizationDisplayName::class);
        Assert::allIsInstanceOf($organizationURL, OrganizationURL::class);

        $this->setExtensions($extensions);
        $this->setAttributesNS($namespacedAttributes);
    }


    /**
     * Collect the value of the OrganizationName property.
     *
     * @return \SimpleSAML\SAML2\XML\md\OrganizationName[]
     */
    public function getOrganizationName(): array
    {
        return $this->organizationName;
    }


    /**
     * Collect the value of the OrganizationDisplayName property.
     *
     * @return \SimpleSAML\SAML2\XML\md\OrganizationDisplayName[]
     */
    public function getOrganizationDisplayName(): array
    {
        return $this->organizationDisplayName;
    }


    /**
     * Collect the value of the OrganizationURL property.
     *
     * @return \SimpleSAML\SAML2\XML\md\OrganizationURL[]
     */
    public function getOrganizationURL(): array
    {
        return $this->organizationURL;
    }


    /**
     * Initialize an Organization element.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Organization', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Organization::NS, InvalidDOMElementException::class);

        $names = OrganizationName::getChildrenOfClass($xml);
        Assert::minCount($names, 1, 'Missing at least one OrganizationName.', MissingElementException::class);

        $displayNames = OrganizationDisplayName::getChildrenOfClass($xml);
        Assert::minCount(
            $displayNames,
            1,
            'Missing at least one OrganizationDisplayName',
            MissingElementException::class,
        );

        $urls = OrganizationURL::getChildrenOfClass($xml);
        Assert::minCount($urls, 1, 'Missing at least one OrganizationURL', MissingElementException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Cannot process more than one md:Extensions element.',
            TooManyElementsException::class,
        );

        return new static(
            $names,
            $displayNames,
            $urls,
            !empty($extensions) ? $extensions[0] : null,
            self::getAttributesNSFromXML($xml),
        );
    }


    /**
     * Convert this Organization to XML.
     *
     * @param \DOMElement|null $parent The element we should add this organization to.
     * @return \DOMElement This Organization-element.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getAttributesNS() as $attr) {
            $attr->toXML($e);
        }

        $this->getExtensions()?->toXML($e);

        foreach ($this->getOrganizationName() as $name) {
            $name->toXML($e);
        }

        foreach ($this->getOrganizationDisplayName() as $displayName) {
            $displayName->toXML($e);
        }

        foreach ($this->getOrganizationURL() as $url) {
            $url->toXML($e);
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            $data['OrganizationName'],
            $data['OrganizationDisplayName'],
            $data['OrganizationURL'],
            $data['Extensions'] ?? null,
            $data['attributes'] ?? [],
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array $data
     * @return array $data
     */
    private static function processArrayContents(array $data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);

        // Make sure the array keys are known for this kind of object
        Assert::allOneOf(
            array_keys($data),
            [
                'organizationname',
                'organizationdisplayname',
                'organizationurl',
                'extensions',
                'attributes',
            ],
            ArrayValidationException::class,
        );

        Assert::keyExists($data, 'organizationname', ArrayValidationException::class);
        Assert::keyExists($data, 'organizationdisplayname', ArrayValidationException::class);
        Assert::keyExists($data, 'organizationurl', ArrayValidationException::class);

        // The minimum count is validated by the constructor
        Assert::isArray($data['organizationname'], ArrayValidationException::class);
        Assert::isArray($data['organizationdisplayname'], ArrayValidationException::class);
        Assert::isArray($data['organizationurl'], ArrayValidationException::class);

        foreach ($data['organizationname'] as $lang => $orgName) {
            $data['organizationname'][$lang] = OrganizationName::fromArray([$lang => $orgName]);
        }

        foreach ($data['organizationdisplayname'] as $lang => $orgDisplayName) {
            $data['organizationdisplayname'][$lang] = OrganizationDisplayName::fromArray([$lang => $orgDisplayName]);
        }

        foreach ($data['organizationurl'] as $lang => $orgUrl) {
            $data['organizationurl'][$lang] = OrganizationURL::fromArray([$lang => $orgUrl]);
        }

        $retval = [
            'OrganizationName' => $data['organizationname'],
            'OrganizationDisplayName' => $data['organizationdisplayname'],
            'OrganizationURL' => $data['organizationurl'],
        ];

        if (array_key_exists('extensions', $data)) {
            Assert::isArray($data['extensions'], ArrayValidationException::class);
            $retval['Extensions'] = new Extensions($data['extensions']);
        }

        if (array_key_exists('attributes', $data)) {
            Assert::isArray($data['attributes'], ArrayValidationException::class);
            Assert::allIsArray($data['attributes'], ArrayValidationException::class);
            foreach ($data['attributes'] as $i => $attr) {
                $retval['attributes'][] = XMLAttribute::fromArray($attr);
            }
        }

        return $retval;
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'OrganizationName' => [],
            'OrganizationDisplayName' => [],
            'OrganizationURL' => [],
            'Extensions' => $this->getExtensions()?->getList(),
            'attributes' => [],
        ];

        foreach ($this->getOrganizationName() as $orgName) {
            $data['OrganizationName'] = array_merge($data['OrganizationName'], $orgName->toArray());
        }

        foreach ($this->getOrganizationDisplayName() as $orgDisplayName) {
            $data['OrganizationDisplayName'] = array_merge(
                $data['OrganizationDisplayName'],
                $orgDisplayName->toArray(),
            );
        }

        foreach ($this->getOrganizationURL() as $orgURL) {
            $data['OrganizationURL'] = array_merge($data['OrganizationURL'], $orgURL->toArray());
        }

        foreach ($this->getAttributesNS() as $attr) {
            $data['attributes'][] = $attr->toArray();
        }

        return array_filter($data);
    }
}
