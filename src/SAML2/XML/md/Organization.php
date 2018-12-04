<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\Chunk;

/**
 * Class representing SAML 2 Organization element.
 *
 * @package SimpleSAMLphp
 */
class Organization
{
    /**
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var array
     */
    public $Extensions = [];

    /**
     * The OrganizationName, as an array of language => translation.
     *
     * @var array
     */
    public $OrganizationName = [];

    /**
     * The OrganizationDisplayName, as an array of language => translation.
     *
     * @var array
     */
    public $OrganizationDisplayName = [];

    /**
     * The OrganizationURL, as an array of language => translation.
     *
     * @var array
     */
    public $OrganizationURL = [];

    /**
     * Initialize an Organization element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $this->setExtensions(Extensions::getList($xml));

        $this->setOrganizationName(Utils::extractLocalizedStrings($xml, Constants::NS_MD, 'OrganizationName'));
        $organizationName = $this->getOrganizationName();
        if (empty($organizationName)) {
            $this->setOrganizationName(['invalid' => '']);
        }

        $this->setOrganizationDisplayName(Utils::extractLocalizedStrings($xml, Constants::NS_MD, 'OrganizationDisplayName'));
        $organizationDisplayName = $this->getOrganizationDisplayName();
        if (empty($organizationDisplayName)) {
            $this->setOrganizationDisplayName(['invalid' => '']);
        }

        $this->setOrganizationURL(Utils::extractLocalizedStrings($xml, Constants::NS_MD, 'OrganizationURL'));
        $organizationURL = $this->getOrganizationURL();
        if (empty($organizationURL)) {
            $this->setOrganizationURL(['invalid' => '']);
        }
    }

    /**
     * Collect the value of the Extensions-property
     * @return \SAML2\XML\Chunk[]
     */
    public function getExtensions()
    {
        return $this->Extensions;
    }

    /**
     * Set the value of the Extensions-property
     * @param array $extensions
     */
    public function setExtensions(array $extensions)
    {
        $this->Extensions = $extensions;
    }

    /**
     * Add an Extension.
     *
     * @param \SAML2\XML\Chunk $extensions The Extensions
     */
    public function addExtension(Extensions $extension)
    {
        $this->Extensions[] = $extension;
    }

    /**
     * Collect the value of the OrganizationName-property
     * @return string[]
     */
    public function getOrganizationName()
    {
        return $this->OrganizationName;
    }

    /**
     * Set the value of the OrganizationName-property
     * @param array $organizationName
     */
    public function setOrganizationName(array $organizationName)
    {
        $this->OrganizationName = $organizationName;
    }

    /**
     * Collect the value of the OrganizationDisplayName-property
     * @return string[]
     */
    public function getOrganizationDisplayName()
    {
        return $this->OrganizationDisplayName;
    }

    /**
     * Set the value of the OrganizationDisplayName-property
     * @param array $organizationDisplayName
     */
    public function setOrganizationDisplayName(array $organizationDisplayName)
    {
        $this->OrganizationDisplayName = $organizationDisplayName;
    }

    /**
     * Collect the value of the OrganizationURL-property
     * @return string[]
     */
    public function getOrganizationURL()
    {
        return $this->OrganizationURL;
    }

    /**
     * Set the value of the OrganizationURL-property
     * @param array $organizationURL
     */
    public function setOrganizationURL(array $organizationURL)
    {
        $this->OrganizationURL = $organizationURL;
    }

    /**
     * Convert this Organization to XML.
     *
     * @param  \DOMElement $parent The element we should add this organization to.
     * @return \DOMElement This Organization-element.
     */
    public function toXML(\DOMElement $parent)
    {
        assert(is_array($this->getExtensions()));
        assert(is_array($organizationName = $this->getOrganizationName()));
        assert(!empty($organizationName));
        assert(is_array($organizationDisplayName = $this->getOrganizationDisplayName()));
        assert(!empty($organizationDisplayName));
        assert(is_array($organizationURL = $this->getOrganizationURL()));
        assert(!empty($organizationURL));

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_MD, 'md:Organization');
        $parent->appendChild($e);

        Extensions::addList($e, $this->getExtensions());

        Utils::addStrings($e, Constants::NS_MD, 'md:OrganizationName', true, $this->getOrganizationName());
        Utils::addStrings($e, Constants::NS_MD, 'md:OrganizationDisplayName', true, $this->getOrganizationDisplayName());
        Utils::addStrings($e, Constants::NS_MD, 'md:OrganizationURL', true, $this->getOrganizationURL());

        return $e;
    }
}
