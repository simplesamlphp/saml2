<?php

declare(strict_types=1);

namespace SAML2\XML\mdui;

use DOMElement;
use SAML2\Utils;
use SAML2\XML\Chunk;
use Webmozart\Assert\Assert;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package SimpleSAMLphp
 */
final class UIInfo extends AbstractMduiElement
{
    /**
     * Array with child elements.
     *
     * The elements can be any of the other \SAML2\XML\mdui\* elements.
     *
     * @var \SAML2\XML\Chunk[]|null
     */
    protected $children = null;

    /**
     * The DisplayName, as an array of language => translation.
     *
     * @var array|null
     */
    protected $DisplayName = null;

    /**
     * The Description, as an array of language => translation.
     *
     * @var array|null
     */
    protected $Description = null;

    /**
     * The InformationURL, as an array of language => url.
     *
     * @var array|null
     */
    protected $InformationURL = null;

    /**
     * The PrivacyStatementURL, as an array of language => url.
     *
     * @var array|null
     */
    protected $PrivacyStatementURL = null;

    /**
     * The Keywords, as an array of Keywords objects
     *
     * @var \SAML2\XML\mdui\Keywords[]|null
     */
    protected $Keywords = null;

    /**
     * The Logo, as an array of Logo objects
     *
     * @var \SAML2\XML\mdui\Logo[]|null
     */
    protected $Logo = null;


    /**
     * Create a UIInfo element.
     *
     * @param array|null $DisplayName
     * @param array|null $Description
     * @param array|null $InformationURL
     * @param array|null $PrivacyStatementURL
     * @param \SAML2\XML\mdui\Keywords[]|null $Keywords
     * @param \SAML2\XML\mdui\Logo[]|null $Logo
     * @param \SAML2\XML\Chunk[]|null $children
     */
    public function __construct(
        array $DisplayName = null,
        array $Description = null,
        array $InformationURL = null,
        array $PrivacyStatementURL = null,
        array $Keywords = null,
        array $Logo = null,
        array $children = null
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
     * @return \SAML2\XML\mdui\Keywords[]|null
     */
    public function getKeywords(): ?array
    {
        return $this->Keywords;
    }


    /**
     * Set the value of the Keywords-property
     *
     * @param \SAML2\XML\mdui\Keywords[]|null $keywords
     * @return void
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    private function setKeywords(?array $keywords): void
    {
        if (!is_null($keywords)) {
            Assert::allIsInstanceOf($keywords, Keywords::class);
        }
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
        $this->Keywords = empty($this->Keywords) ? [$keyword] : array_merge($this->Keywords, [$keyword]);
    }


    /**
     * Collect the value of the DisplayName-property
     *
     * @return string[]|null
     */
    public function getDisplayName(): ?array
    {
        return $this->DisplayName;
    }


    /**
     * Set the value of the DisplayName-property
     *
     * @param array|null $displayName
     * @return void
     */
    private function setDisplayName(?array $displayName): void
    {
        $this->DisplayName = $displayName;
    }


    /**
     * Collect the value of the Description-property
     *
     * @return string[]|null
     */
    public function getDescription(): ?array
    {
        return $this->Description;
    }


    /**
     * Set the value of the Description-property
     *
     * @param array|null $description
     * @return void
     */
    private function setDescription(?array $description): void
    {
        $this->Description = $description;
    }


    /**
     * Collect the value of the InformationURL-property
     * @return string[]|null
     */
    public function getInformationURL(): ?array
    {
        return $this->InformationURL;
    }


    /**
     * Set the value of the InformationURL-property
     *
     * @param array|null $informationURL
     * @return void
     */
    private function setInformationURL(?array $informationURL): void
    {
        $this->InformationURL = $informationURL;
    }


    /**
     * Collect the value of the PrivacyStatementURL-property
     *
     * @return string[]|null
     */
    public function getPrivacyStatementURL(): ?array
    {
        return $this->PrivacyStatementURL;
    }


    /**
     * Set the value of the PrivacyStatementURL-property
     *
     * @param array|null $privacyStatementURL
     * @return void
     */
    private function setPrivacyStatementURL(?array $privacyStatementURL): void
    {
        $this->PrivacyStatementURL = $privacyStatementURL;
    }


    /**
     * Collect the value of the Logo-property
     *
     * @return \SAML2\XML\mdui\Logo[]|null
     */
    public function getLogo(): ?array
    {
        return $this->Logo;
    }


    /**
     * Set the value of the Logo-property
     *
     * @param \SAML2\XML\mdui\Logo[]|null $logo
     * @return void
     */
    private function setLogo(?array $logo): void
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
     * @return \SAML2\XML\Chunk[]|null
     */
    public function getChildren(): ?array
    {
        return $this->children;
    }


    /**
     * Set the value of the childen-property
     *
     * @param array|null $children
     * @return void
     */
    private function setChildren(?array $children): void
    {
        $this->children = $children;
    }


    /**
     * Add the value to the children-property
     *
     * @param \SAML2\XML\Chunk $child
     * @return void
     */
    public function addChild(Chunk $child): void
    {
        $this->children = empty($this->children) ? [$child] : array_merge($this->children, [$child]);
    }


    /**
     * Convert XML into a UIInfo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'UIInfo');
        Assert::same($xml->namespaceURI, UIInfo::NS);

        $DisplayName = Utils::extractLocalizedStrings($xml, UIInfo::NS, 'DisplayName');
        $Description = Utils::extractLocalizedStrings($xml, UIInfo::NS, 'Description');
        $InformationURL = Utils::extractLocalizedStrings($xml, UIInfo::NS, 'InformationURL');
        $PrivacyStatementURL = Utils::extractLocalizedStrings($xml, UIInfo::NS, 'PrivacyStatementURL');
        $Keywords = $Logo = $children = [];

        /** @var \DOMElement $node */
        foreach (Utils::xpQuery($xml, './*') as $node) {
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
     * @return \DOMElement|null
     */
    public function toXML(DOMElement $parent = null): ?DOMElement
    {
        if (
            !empty($this->DisplayName)
            || !empty($this->Description)
            || !empty($this->InformationURL)
            || !empty($this->PrivacyStatementURL)
            || !empty($this->Keywords)
            || !empty($this->Logo)
            || !empty($this->children)
        ) {
            $e = $this->instantiateParentElement($parent);

            if (!empty($this->DisplayName)) {
                Utils::addStrings($e, UIInfo::NS, 'mdui:DisplayName', true, $this->DisplayName);
            }

            if (!empty($this->Description)) {
                Utils::addStrings($e, UIInfo::NS, 'mdui:Description', true, $this->Description);
            }

            if (!empty($this->InformationURL)) {
                Utils::addStrings($e, UIInfo::NS, 'mdui:InformationURL', true, $this->InformationURL);
            }

            if (!empty($this->PrivacyStatementURL)) {
                Utils::addStrings($e, UIInfo::NS, 'mdui:PrivacyStatementURL', true, $this->PrivacyStatementURL);
            }

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

            return $e;
        }

        return null;
    }
}
