<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use DOMElement;
use SAML2\Constants as C;
use SAML2\Utils\XPath;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package SimpleSAMLphp
 */
class UIInfo
{
    /**
     * Array with child elements.
     *
     * The elements can be any of the other \SAML2\XML\mdui\* elements.
     *
     * @var \SimpleSAML\XML\Chunk[]
     */
    private array $children = [];

    /**
     * The DisplayName, as an array of language => translation.
     *
     * @var array
     */
    private array $DisplayName = [];

    /**
     * The Description, as an array of language => translation.
     *
     * @var array
     */
    private array $Description = [];

    /**
     * The InformationURL, as an array of language => url.
     *
     * @var array
     */
    private array $InformationURL = [];

    /**
     * The PrivacyStatementURL, as an array of language => url.
     *
     * @var array
     */
    private array $PrivacyStatementURL = [];

    /**
     * The Keywords, as an array of Keywords objects
     *
     * @var \SAML2\XML\mdui\Keywords[]
     */
    private array $Keywords = [];

    /**
     * The Logo, as an array of Logo objects
     *
     * @var \SAML2\XML\mdui\Logo[]
     */
    private array $Logo = [];


    /**
     * Create a UIInfo element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $this->DisplayName = XMLUtils::extractLocalizedStrings($xml, C::NS_MDUI, 'DisplayName');
        $this->Description = XMLUtils::extractLocalizedStrings($xml, C::NS_MDUI, 'Description');
        $this->InformationURL = XMLUtils::extractLocalizedStrings($xml, C::NS_MDUI, 'InformationURL');
        $this->PrivacyStatementURL = XMLUtils::extractLocalizedStrings($xml, C::NS_MDUI, 'PrivacyStatementURL');

        $xpCache = XPath::getXPath($xml);
        /** @var \DOMElement $node */
        foreach (XPath::xpQuery($xml, './*', $xpCache) as $node) {
            if ($node->namespaceURI === C::NS_MDUI) {
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
     * Collect the value of the Keywords-property
     *
     * @return \SAML2\XML\mdui\Keywords[]
     */
    public function getKeywords(): array
    {
        return $this->Keywords;
    }


    /**
     * Set the value of the Keywords-property
     *
     * @param \SAML2\XML\mdui\Keywords[] $keywords
     * @return void
     */
    public function setKeywords(array $keywords): void
    {
        Assert::allIsInstanceOf($keywords, Keywords::class);
        $this->Keywords = $keywords;
    }


    /**
     * Add the value to the Keywords-property
     *
     * @param \SAML2\XML\mdui\Keywords $keyword
     * @return void
     */
    public function addKeyword(Keywords $keyword): void
    {
        $this->Keywords[] = $keyword;
    }


    /**
     * Collect the value of the DisplayName-property
     *
     * @return string[]
     */
    public function getDisplayName(): array
    {
        return $this->DisplayName;
    }


    /**
     * Set the value of the DisplayName-property
     *
     * @param array $displayName
     * @return void
     */
    public function setDisplayName(array $displayName): void
    {
        $this->DisplayName = $displayName;
    }


    /**
     * Collect the value of the Description-property
     *
     * @return string[]
     */
    public function getDescription(): array
    {
        return $this->Description;
    }


    /**
     * Set the value of the Description-property
     *
     * @param array $description
     * @return void
     */
    public function setDescription(array $description): void
    {
        $this->Description = $description;
    }


    /**
     * Collect the value of the InformationURL-property
     * @return string[]
     */
    public function getInformationURL(): array
    {
        return $this->InformationURL;
    }


    /**
     * Set the value of the InformationURL-property
     *
     * @param array $informationURL
     * @return void
     */
    public function setInformationURL(array $informationURL): void
    {
        $this->InformationURL = $informationURL;
    }


    /**
     * Collect the value of the PrivacyStatementURL-property
     *
     * @return string[]
     */
    public function getPrivacyStatementURL(): array
    {
        return $this->PrivacyStatementURL;
    }


    /**
     * Set the value of the PrivacyStatementURL-property
     *
     * @param array $privacyStatementURL
     * @return void
     */
    public function setPrivacyStatementURL(array $privacyStatementURL): void
    {
        $this->PrivacyStatementURL = $privacyStatementURL;
    }


    /**
     * Collect the value of the Logo-property
     *
     * @return \SAML2\XML\mdui\Logo[]
     */
    public function getLogo(): array
    {
        return $this->Logo;
    }


    /**
     * Set the value of the Logo-property
     *
     * @param \SAML2\XML\mdui\Logo[] $logo
     * @return void
     */
    public function setLogo(array $logo): void
    {
        $this->Logo = $logo;
    }


    /**
     * Add the value to the Logo-property
     *
     * @param \SAML2\XML\mdui\Logo $logo
     * @return void
     */
    public function addLogo(Logo $logo): void
    {
        $this->Logo[] = $logo;
    }


    /**
     * Collect the value of the children-property
     *
     * @return \SimpleSAML\XML\Chunk[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }


    /**
     * Set the value of the childen-property
     *
     * @param array $children
     * @return void
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }


    /**
     * Add the value to the children-property
     *
     * @param \SimpleSAML\XML\Chunk $child
     * @return void
     */
    public function addChildren(Chunk $child): void
    {
        $this->children[] = $child;
    }


    /**
     * Convert this UIInfo to XML.
     *
     * @param \DOMElement $parent The element we should append to.
     * @return \DOMElement|null
     */
    public function toXML(DOMElement $parent): ?DOMElement
    {
        $e = null;
        if (
            !empty($this->DisplayName)
            || !empty($this->Description)
            || !empty($this->InformationURL)
            || !empty($this->PrivacyStatementURL)
            || !empty($this->Keywords)
            || !empty($this->Logo)
            || !empty($this->children)
        ) {
            $doc = $parent->ownerDocument;

            $e = $doc->createElementNS(C::NS_MDUI, 'mdui:UIInfo');
            $parent->appendChild($e);

            XMLUtils::addStrings($e, C::NS_MDUI, 'mdui:DisplayName', true, $this->DisplayName);
            XMLUtils::addStrings($e, C::NS_MDUI, 'mdui:Description', true, $this->Description);
            XMLUtils::addStrings($e, C::NS_MDUI, 'mdui:InformationURL', true, $this->InformationURL);
            XMLUtils::addStrings($e, C::NS_MDUI, 'mdui:PrivacyStatementURL', true, $this->PrivacyStatementURL);

            foreach ($this->Keywords as $child) {
                $child->toXML($e);
            }

            foreach ($this->Logo as $child) {
                $child->toXML($e);
            }

            foreach ($this->children as $child) {
                $child->toXML($e);
            }
        }

        return $e;
    }
}
