<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\Utils;

/**
 * Class representing SAML 2 Organization element.
 *
 * @package SimpleSAMLphp
 */
final class Organization
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

        $this->Extensions = Extensions::getList($xml);

        $this->OrganizationName = Utils::extractLocalizedStrings($xml, Constants::NS_MD, 'OrganizationName');
        if (empty($this->OrganizationName)) {
            $this->OrganizationName = ['invalid' => ''];
        }

        $this->OrganizationDisplayName = Utils::extractLocalizedStrings($xml, Constants::NS_MD, 'OrganizationDisplayName');
        if (empty($this->OrganizationDisplayName)) {
            $this->OrganizationDisplayName = ['invalid' => ''];
        }

        $this->OrganizationURL = Utils::extractLocalizedStrings($xml, Constants::NS_MD, 'OrganizationURL');
        if (empty($this->OrganizationURL)) {
            $this->OrganizationURL = ['invalid' => ''];
        }
    }

    /**
     * Convert this Organization to XML.
     *
     * @param  \DOMElement $parent The element we should add this organization to.
     * @return \DOMElement This Organization-element.
     */
    public function toXML(\DOMElement $parent)
    {
        assert(is_array($this->Extensions));
        assert(is_array($this->OrganizationName));
        assert(!empty($this->OrganizationName));
        assert(is_array($this->OrganizationDisplayName));
        assert(!empty($this->OrganizationDisplayName));
        assert(is_array($this->OrganizationURL));
        assert(!empty($this->OrganizationURL));

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_MD, 'md:Organization');
        $parent->appendChild($e);

        Extensions::addList($e, $this->Extensions);

        Utils::addStrings($e, Constants::NS_MD, 'md:OrganizationName', true, $this->OrganizationName);
        Utils::addStrings($e, Constants::NS_MD, 'md:OrganizationDisplayName', true, $this->OrganizationDisplayName);
        Utils::addStrings($e, Constants::NS_MD, 'md:OrganizationURL', true, $this->OrganizationURL);

        return $e;
    }
}
