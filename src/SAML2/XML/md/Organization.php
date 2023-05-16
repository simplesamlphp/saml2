<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMDocument;
use DOMElement;
use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\ExtendableElementTrait;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XML\Utils as XMLUtils;

use function array_key_exists;
use function array_merge;

/**
 * Class representing SAML 2 Organization element.
 *
 * @package simplesamlphp/saml2
 */
final class Organization extends AbstractMdElement
{
    use ExtendableAttributesTrait;
    use ExtendableElementTrait;


    /**
     * Organization constructor.
     *
     * @param \SimpleSAML\SAML2\XML\md\OrganizationName[] $organizationName
     * @param \SimpleSAML\SAML2\XML\md\OrganizationDisplayName[] $organizationDisplayName
     * @param \SimpleSAML\SAML2\XML\md\OrganizationURL[] $organizationURL
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param \DOMAttr[] $namespacedAttributes
     */
    public function __construct(
        protected array $organizationName,
        protected array $organizationDisplayName,
        protected array $organizationURL,
        ?Extensions $extensions = null,
        array $namespacedAttributes = [],
    ) {
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
     * @return self
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
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getAttributesNS() as $attr) {
            $e->setAttributeNS($attr['namespaceURI'], $attr['qualifiedName'], $attr['value']);
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
        $orgNames = [];
        if (array_key_exists('OrganizationName', $data)) {
            Assert::count($data['OrganizationName'], 1);
            $orgNames[] = OrganizationName::fromArray($data['OrganizationName']);
        }

        $orgDisplayNames = [];
        if (array_key_exists('OrganizationDisplayName', $data)) {
            Assert::count($data['OrganizationDisplayName'], 1);
            $orgDisplayNames[] = OrganizationDisplayName::fromArray($data['OrganizationDisplayName']);
        }

        $orgURLs = [];
        if (array_key_exists('OrganizationURL', $data)) {
            Assert::count($data['OrganizationURL'], 1);
            $orgURLs[] = OrganizationURL::fromArray($data['OrganizationURL']);
        }

        $Extensions = $data['Extensions'] ?? null;

        // Anything after this should be (namespaced) attributes
        unset(
            $data['OrganizationName'],
            $data['OrganizationDisplayName'],
            $data['OrganizationURL'],
            $data['Extensions'],
        );

        $attributes = [];
        foreach ($data as $ns => $attribute) {
            $name = array_key_first($attribute);
            $value = $attribute[$name];

            $doc = new DOMDocument('1.0', 'UTF-8');
            $elt = $doc->createElement("placeholder");
            $elt->setAttributeNS($ns, $name, $value);

            $attributes[] = $elt->getAttributeNode($name);
        }

        return new static(
            $orgNames,
            $orgDisplayNames,
            $orgURLs,
            $Extensions,
            $attributes,
        );
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
            'Extensions' => $this->getExtensions(),
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

        /** @psalm-suppress PossiblyNullReference */
        foreach ($this->getAttributesNS() as $a) {
            $data[$a['namespaceURI']] = [$a['qualifiedName'] => $a['value']];
        }

        return $data;
    }
}
