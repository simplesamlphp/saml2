<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use Exception;
use SAML2\Constants;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Exception\MissingElementException;
use SAML2\Utils;
use SAML2\XML\ExtendableAttributesTrait;
use SAML2\XML\ExtendableElementTrait;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SAML 2 Organization element.
 *
 * @package SimpleSAMLphp
 */
final class Organization extends AbstractMdElement
{
    use ExtendableAttributesTrait;
    use ExtendableElementTrait;

    /**
     * The OrganizationName, as an array of language => translation.
     *
     * @var \SAML2\XML\md\OrganizationName[]
     */
    protected $OrganizationName = [];

    /**
     * The OrganizationDisplayName, as an array of language => translation.
     *
     * @var \SAML2\XML\md\OrganizationDisplayName[]
     */
    protected $OrganizationDisplayName = [];

    /**
     * The OrganizationURL, as an array of language => translation.
     *
     * @var array
     */
    protected $OrganizationURL = [];


    /**
     * Organization constructor.
     *
     * @param \SAML2\XML\md\OrganizationName[] $organizationName
     * @param \SAML2\XML\md\OrganizationDisplayName[] $organizationDisplayName
     * @param string[] $organizationURL
     * @param \SAML2\XML\md\Extensions|null $extensions
     */
    public function __construct(
        array $organizationName,
        array $organizationDisplayName,
        array $organizationURL,
        ?Extensions $extensions = null
    ) {
        $this->setOrganizationName($organizationName);
        $this->setOrganizationDisplayName($organizationDisplayName);
        $this->setOrganizationURL($organizationURL);
        $this->setExtensions($extensions);
    }


    /**
     * Collect the value of the OrganizationName property.
     *
     * @return \SAML2\XML\md\OrganizationName[]
     */
    public function getOrganizationName(): array
    {
        return $this->OrganizationName;
    }


    /**
     * Set the value of the OrganizationName property.
     *
     * @param \SAML2\XML\md\OrganizationName[] $organizationName
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setOrganizationName(array $organizationName): void
    {
        Assert::allIsInstanceOf($organizationName, OrganizationName::class);
        $this->OrganizationName = $organizationName;
    }


    /**
     * Collect the value of the OrganizationDisplayName property.
     *
     * @return \SAML2\XML\md\OrganizationDisplayName[]
     */
    public function getOrganizationDisplayName(): array
    {
        return $this->OrganizationDisplayName;
    }


    /**
     * Set the value of the OrganizationDisplayName property.
     *
     * @param \SAML2\XML\md\OrganizationDisplayName[] $organizationDisplayName
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setOrganizationDisplayName(array $organizationDisplayName): void
    {
        Assert::allIsInstanceOf($organizationDisplayName, OrganizationDisplayName::class);
        $this->OrganizationDisplayName = $organizationDisplayName;
    }


    /**
     * Collect the value of the OrganizationURL property.
     *
     * @return string[]
     */
    public function getOrganizationURL(): array
    {
        return $this->OrganizationURL;
    }


    /**
     * Set the value of the OrganizationURL property.
     *
     * @param string[] $organizationURL
     * @throws \InvalidArgumentException
     */
    protected function setOrganizationURL(array $organizationURL): void
    {
        Assert::allStringNotEmpty($organizationURL, 'Incorrect OrganizationURL.');
        $this->OrganizationURL = $organizationURL;
    }


    /**
     * Initialize an Organization element.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return self
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SAML2\Exception\MissingElementException if one of the mandatory child-elements is missing
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Organization', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Organization::NS, InvalidDOMElementException::class);

        $names = OrganizationName::getChildrenOfClass($xml);
        Assert::minCount($names, 1, 'Missing at least one OrganizationName.', MissingElementException::class);

        $displayNames = OrganizationDisplayName::getChildrenOfClass($xml);
        Assert::minCount($displayNames, 1, 'Missing at least one OrganizationDisplayName', MissingElementException::class);

        $url = Utils::extractLocalizedStrings($xml, Constants::NS_MD, 'OrganizationURL');
        Assert::allStringNotEmpty($url, 'No localized organization URL found.', MissingElementException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Cannot process more than one md:Extensions element.');

        return new self($names, $displayNames, $url, !empty($extensions) ? $extensions[0] : null);
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

        if ($this->Extensions !== null) {
            $this->Extensions->toXML($e);
        }

        foreach ($this->OrganizationName as $name) {
            $name->toXML($e);
        }

        foreach ($this->OrganizationDisplayName as $displayName) {
            $displayName->toXML($e);
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:OrganizationURL', true, $this->OrganizationURL);

        return $e;
    }
}
