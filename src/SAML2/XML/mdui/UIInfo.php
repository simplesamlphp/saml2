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
     * The DisplayName, as an array of DisplayName objects
     *
     * @var \SimpleSAML\SAML2\XML\mdui\DisplayName[]
     */
    protected array $DisplayName = [];

    /**
     * The Description, as an array of Description objects
     *
     * @var \SimpleSAML\SAML2\XML\mdui\Description[]
     */
    protected array $Description = [];

    /**
     * The InformationURL, as an array of InformationURL objects
     *
     * @var \SimpleSAML\SAML2\XML\mdui\InformationURL[]
     */
    protected array $InformationURL = [];

    /**
     * The PrivacyStatementURL, as an array of PrivacyStatementURL objects
     *
     * @var \SimpleSAML\SAML2\XML\mdui\PrivacyStatementURL[]
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
     * @param \SimpleSAML\SAML2\XML\mdui\DisplayName[] $DisplayName
     * @param \SimpleSAML\SAML2\XML\mdui\Description[] $Description
     * @param \SimpleSAML\SAML2\XML\mdui\InformationURL[] $InformationURL
     * @param \SimpleSAML\SAML2\XML\mdui\PrivacyStatementURL[] $PrivacyStatementURL
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
     * @return \SimpleSAML\SAML2\XML\mdui\DisplayName[]
     */
    public function getDisplayName(): array
    {
        return $this->DisplayName;
    }


    /**
     * Set the value of the DisplayName-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\DisplayName[] $displayName
     */
    private function setDisplayName(array $displayName): void
    {
        Assert::allIsInstanceOf($displayName, DisplayName::class);

        $this->DisplayName = $displayName;
    }


    /**
     * Collect the value of the Description-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\Description[]
     */
    public function getDescription(): array
    {
        return $this->Description;
    }


    /**
     * Set the value of the Description-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\Description[] $description
     */
    private function setDescription(array $description): void
    {
        Assert::allIsInstanceOf($description, Description::class);

        $this->Description = $description;
    }


    /**
     * Collect the value of the InformationURL-property
     * @return \SimpleSAML\SAML2\XML\mdui\InformationURL[]
     */
    public function getInformationURL(): array
    {
        return $this->InformationURL;
    }


    /**
     * Set the value of the InformationURL-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\InformationURL[] $informationURL
     */
    private function setInformationURL(array $informationURL): void
    {
        Assert::allIsInstanceOf($informationURL, InformationURL::class);

        $this->InformationURL = $informationURL;
    }


    /**
     * Collect the value of the PrivacyStatementURL-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\PrivacyStatementURL[]
     */
    public function getPrivacyStatementURL(): array
    {
        return $this->PrivacyStatementURL;
    }


    /**
     * Set the value of the PrivacyStatementURL-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\PrivacyStatementURL[] $privacyStatementURL
     */
    private function setPrivacyStatementURL(array $privacyStatementURL): void
    {
        Assert::allIsInstanceOf($privacyStatementURL, PrivacyStatementURL::class);

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

        $DisplayName = DisplayName::getChildrenOfClass($xml);
        $Description = Description::getChildrenOfClass($xml);
        $InformationURL = InformationURL::getChildrenOfClass($xml);
        $PrivacyStatementURL = PrivacyStatementURL::getChildrenOfClass($xml);
        $Keywords = Keywords::getChildrenOfClass($xml);
        $Logo = Logo::getChildrenOfClass($xml);
        $children = [];

        /** @var \DOMElement $node */
        foreach (XMLUtils::xpQuery($xml, './*') as $node) {
            if ($node->namespaceURI !== UIInfo::NS) {
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

        foreach ($this->DisplayName as $child) {
            $child->toXML($e);
        }

        foreach ($this->Description as $child) {
            $child->toXML($e);
        }

        foreach ($this->InformationURL as $child) {
            $child->toXML($e);
        }

        foreach ($this->PrivacyStatementURL as $child) {
            $child->toXML($e);
        }

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
