<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class UIInfo extends AbstractMduiElement
{
    /**
     * Array with child elements.
     *
     * The elements can be any of the other \SAML2\XML\mdui\* elements.
     *
     * @var \SimpleSAML\XML\Chunk[]
     */
    protected array $children = [];

    /**
     * The DisplayName, as an array of language => translation.
     *
     * @var string[]
     */
    protected array $DisplayName = [];

    /**
     * The Description, as an array of language => translation.
     *
     * @var string[]
     */
    protected array $Description = [];

    /**
     * The InformationURL, as an array of language => url.
     *
     * @var string[]
     */
    protected array $InformationURL = [];

    /**
     * The PrivacyStatementURL, as an array of language => url.
     *
     * @var array
     */
    protected array $PrivacyStatementURL = [];

    /**
     * The Keywords, as an array of Keywords objects
     *
     * @var \SimpleSAML\SAML2\XML\mdui\Keywords[]
     */
    protected array $Keywords = [];

    /**
     * The Logo, as an array of Logo objects
     *
     * @var \SimpleSAML\SAML2\XML\mdui\Logo[]
     */
    protected array $Logo = [];


    /**
     * Create a UIInfo element.
     *
     * @param string[] $DisplayName
     * @param string[] $Description
     * @param string[] $InformationURL
     * @param string[] $PrivacyStatementURL
     * @param \SimpleSAML\SAML2\XML\mdui\Keywords[] $Keywords
     * @param \SimpleSAML\SAML2\XML\mdui\Logo[] $Logo
     * @param \SimpleSAML\XML\Chunk[] $children
     */
    public function __construct(
        array $DisplayName = [],
        array $Description = [],
        array $InformationURL = [],
        array $PrivacyStatementURL = [],
        array $Keywords = [],
        array $Logo = [],
        array $children = []
    ) {
        $this->setDisplayName($DisplayName);
        $this->setDescription($Description);
        $this->setInformationURL($InformationURL);
        $this->setPrivacyStatementURL($PrivacyStatementURL);
        $this->setKeywords($Keywords);
        $this->setLogo($Logo);
        $this->setChildren($children);
    }


    /**
     * Collect the value of the Keywords-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\Keywords[]
     */
    public function getKeywords(): array
    {
        return $this->Keywords;
    }


    /**
     * Set the value of the Keywords-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\Keywords[] $keywords
     *
     * @throws \SimpleSAML\Assert\AssertionFailedException if assertions are false
     */
    private function setKeywords(array $keywords): void
    {
        Assert::allIsInstanceOf($keywords, Keywords::class);

        $this->Keywords = $keywords;
    }


    /**
     * Add the value to the Keywords-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\Keywords $keyword
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
     * @param string[] $displayName
     */
    private function setDisplayName(array $displayName): void
    {
        Assert::allStringNotEmpty($displayName);

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
     * @param string[] $description
     */
    private function setDescription(array $description): void
    {
        Assert::allStringNotEmpty($description);

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
     * @param string[] $informationURL
     */
    private function setInformationURL(array $informationURL): void
    {
        Assert::allStringNotEmpty($informationURL);

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
     * @param string[] $privacyStatementURL
     */
    private function setPrivacyStatementURL(array $privacyStatementURL): void
    {
        Assert::allStringNotEmpty($privacyStatementURL);

        $this->PrivacyStatementURL = $privacyStatementURL;
    }


    /**
     * Collect the value of the Logo-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\Logo[]
     */
    public function getLogo(): array
    {
        return $this->Logo;
    }


    /**
     * Set the value of the Logo-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\Logo[] $logo
     */
    private function setLogo(array $logo): void
    {
        Assert::allIsInstanceOf($logo, Logo::class);

        $this->Logo = $logo;
    }


    /**
     * Add the value to the Logo-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\Logo $logo
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
     * @param \SimpleSAML\XML\Chunk[] $children
     */
    private function setChildren(array $children): void
    {
        Assert::allIsInstanceOf($children, Chunk::class);

        $this->children = $children;
    }


    /**
     * Add the value to the children-property
     *
     * @param \SimpleSAML\XML\Chunk $child
     */
    public function addChild(Chunk $child): void
    {
        $this->children[] = $child;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return (
            empty($this->DisplayName)
            && empty($this->Description)
            && empty($this->InformationURL)
            && empty($this->PrivacyStatementURL)
            && empty($this->Keywords)
            && empty($this->Logo)
            && empty($this->children)
        );
    }


    /**
     * Convert XML into a UIInfo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'UIInfo', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, UIInfo::NS, InvalidDOMElementException::class);

        $DisplayName = XMLUtils::extractLocalizedStrings($xml, UIInfo::NS, 'DisplayName');
        $Description = XMLUtils::extractLocalizedStrings($xml, UIInfo::NS, 'Description');
        $InformationURL = XMLUtils::extractLocalizedStrings($xml, UIInfo::NS, 'InformationURL');
        $PrivacyStatementURL = XMLUtils::extractLocalizedStrings($xml, UIInfo::NS, 'PrivacyStatementURL');
        $Keywords = $Logo = $children = [];

        /** @var \DOMElement $node */
        foreach (XMLUtils::xpQuery($xml, './*') as $node) {
            if ($node->namespaceURI === UIInfo::NS) {
                switch ($node->localName) {
                    case 'Keywords':
                        $Keywords[] = Keywords::fromXML($node);
                        break;
                    case 'Logo':
                        $Logo[] = Logo::fromXML($node);
                        break;
                }
            } else {
                $children[] = new Chunk($node);
            }
        }

        return new self(
            $DisplayName,
            $Description,
            $InformationURL,
            $PrivacyStatementURL,
            $Keywords,
            $Logo,
            $children
        );
    }


    /**
     * Convert this UIInfo to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        XMLUtils::addStrings($e, UIInfo::NS, 'mdui:DisplayName', true, $this->DisplayName);
        XMLUtils::addStrings($e, UIInfo::NS, 'mdui:Description', true, $this->Description);
        XMLUtils::addStrings($e, UIInfo::NS, 'mdui:InformationURL', true, $this->InformationURL);
        XMLUtils::addStrings($e, UIInfo::NS, 'mdui:PrivacyStatementURL', true, $this->PrivacyStatementURL);

        foreach ($this->Keywords as $child) {
            $child->toXML($e);
        }

        foreach ($this->Logo as $child) {
            $child->toXML($e);
        }

        foreach ($this->children as $child) {
            $child->toXML($e);
        }

        return $e;
    }
}
