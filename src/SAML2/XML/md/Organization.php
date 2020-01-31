<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use Exception;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\ExtendableAttributesTrait;
use SAML2\XML\ExtendableElementTrait;
use Webmozart\Assert\Assert;

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
     * @param array $organizationURL
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
     * Initialize an Organization element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     *
     * @return self
     * @throws \Exception if the XML lacks any of the mandatory elements.
     */
    public static function fromXML(DOMElement $xml): object
    {
        $names = OrganizationName::getChildrenOfClass($xml);
        Assert::minCount($names, 1, 'Missing at least one OrganizationName.');

        $displayNames = OrganizationDisplayName::getChildrenOfClass($xml);
        Assert::minCount($displayNames, 1, 'Missing at least one OrganizationDisplayName');

        $url = Utils::extractLocalizedStrings($xml, Constants::NS_MD, 'OrganizationURL');
        if (empty($url)) {
            throw new Exception('No localized organization URL found.');
        }

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Cannot process more than one md:Extensions element.');

        return new self($names, $displayNames, $url, !empty($extensions) ? $extensions[0] : null);
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
     * @param array $organizationURL
     */
    protected function setOrganizationURL(array $organizationURL): void
    {
        Assert::allStringNotEmpty($organizationURL, 'Incorrect OrganizationURL.');
        $this->OrganizationURL = $organizationURL;
    }


    /**
     * Convert this Organization to XML.
     *
     * @param \DOMElement $parent The element we should add this organization to.
     *
     * @return \DOMElement This Organization-element.
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->OrganizationName as $name) {
            $name->toXML($e);
        }
        foreach ($this->OrganizationDisplayName as $displayName) {
            $displayName->toXML($e);
        }

        Utils::addStrings($e, Constants::NS_MD, 'md:OrganizationURL', true, $this->OrganizationURL);

        if ($this->Extensions !== null) {
            $this->Extensions->toXML($e);
        }

        return $e;
    }
}
