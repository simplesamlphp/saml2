<?php

namespace SAML2\XML\mdui;

use SAML2\Utils;
use SAML2\XML\Chunk;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package SimpleSAMLphp
 */
class UIInfo
{
    /**
     * The namespace used for the UIInfo extension.
     */
    const NS = 'urn:oasis:names:tc:SAML:metadata:ui';

    /**
     * Array with child elements.
     *
     * The elements can be any of the other \SAML2\XML\mdui\* elements.
     *
     * @var array
     */
    public $children = array();

    /**
     * The DisplayName, as an array of language => translation.
     *
     * @var array
     */
    public $DisplayName = array();

    /**
     * The Description, as an array of language => translation.
     *
     * @var array
     */
    public $Description = array();

    /**
     * The InformationURL, as an array of language => url.
     *
     * @var array
     */
    public $InformationURL = array();

    /**
     * The PrivacyStatementURL, as an array of language => url.
     *
     * @var array
     */
    public $PrivacyStatementURL = array();

    /**
     * The Keywords, as an array of language => array of strings.
     *
     * @var array
     */
    public $Keywords = array();

    /**
     * The Logo, as an array of associative arrays containing url, width, height, and optional lang.
     *
     * @var array
     */
    public $Logo = array();

    /**
     * Create a UIInfo element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $this->DisplayName         = Utils::extractLocalizedStrings($xml, self::NS, 'DisplayName');
        $this->Description         = Utils::extractLocalizedStrings($xml, self::NS, 'Description');
        $this->InformationURL      = Utils::extractLocalizedStrings($xml, self::NS, 'InformationURL');
        $this->PrivacyStatementURL = Utils::extractLocalizedStrings($xml, self::NS, 'PrivacyStatementURL');

        foreach (Utils::xpQuery($xml, './*') as $node) {
            if ($node->namespaceURI === self::NS) {
                switch ($node->localName) {
                    case 'Keywords':
                        $this->Keywords[] = new Keywords($node);
                        break;
                    case 'Logo':
                        $this->Logo[] = new Logo($node);
                        break;
                }
            } else {
                $this->children[] = new Chunk($node);
            }
        }
    }

    /**
     * Convert this UIInfo to XML.
     *
     * @param \DOMElement $parent The element we should append to.
     * @return \DOMElement|null
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_array($this->DisplayName)');
        assert('is_array($this->InformationURL)');
        assert('is_array($this->PrivacyStatementURL)');
        assert('is_array($this->Keywords)');
        assert('is_array($this->Logo)');
        assert('is_array($this->children)');

        $e = null;
        if (!empty($this->DisplayName)
         || !empty($this->Description)
         || !empty($this->InformationURL)
         || !empty($this->PrivacyStatementURL)
         || !empty($this->Keywords)
         || !empty($this->Logo)
         || !empty($this->children)) {
            $doc = $parent->ownerDocument;

            $e = $doc->createElementNS(self::NS, 'mdui:UIInfo');
            $parent->appendChild($e);

            Utils::addStrings($e, self::NS, 'mdui:DisplayName', true, $this->DisplayName);
            Utils::addStrings($e, self::NS, 'mdui:Description', true, $this->Description);
            Utils::addStrings($e, self::NS, 'mdui:InformationURL', true, $this->InformationURL);
            Utils::addStrings($e, self::NS, 'mdui:PrivacyStatementURL', true, $this->PrivacyStatementURL);

            if (!empty($this->Keywords)) {
                foreach ($this->Keywords as $child) {
                    $child->toXML($e);
                }
            }

            if (!empty($this->Logo)) {
                foreach ($this->Logo as $child) {
                    $child->toXML($e);
                }
            }

            if (!empty($this->children)) {
                foreach ($this->children as $child) {
                    $child->toXML($e);
                }
            }
        }

        return $e;
    }
}
