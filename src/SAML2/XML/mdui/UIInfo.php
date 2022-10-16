<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\ExtendableElementTrait;

use function array_map;
use function array_merge;
use function array_unique;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class UIInfo extends AbstractMduiElement
{
    use ExtendableElementTrait;

    /** The namespace-attribute for the xs:any element */
    public const NAMESPACE = C::XS_ANY_NS_OTHER;

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
        $this->setElements($children);
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

        /**
         * 2.1.4:  There MUST NOT be more than one <mdui:Keywords>,
         *         within a given <mdui:UIInfo>, for a given language
         */
        $this->testLocalizedElements($keywords);

        $this->Keywords = $keywords;
    }


    /**
     * Add the value to the Keywords-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\Keywords $keyword
     */
    public function addKeyword(Keywords $keyword): void
    {
        $this->setKeywords(array_merge($this->Keywords, [$keyword]));
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

        /**
         * 2.1.2:  There MUST NOT be more than one <mdui:DisplayName>,
         *         within a given <mdui:UIInfo>, for a given language
         */
        $this->testLocalizedElements($displayName);

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

        /**
         * 2.1.3:  There MUST NOT be more than one <mdui:Description>,
         *         within a given <mdui:UIInfo>, for a given language
         */
        $this->testLocalizedElements($description);

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

        /**
         * 2.1.6:  There MUST NOT be more than one <mdui:InformationURL>,
         *         within a given <mdui:UIInfo>, for a given language
         */
        $this->testLocalizedElements($informationURL);

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

        /**
         * 2.1.7:  There MUST NOT be more than one <mdui:PrivacyStatementURL>,
         *         within a given <mdui:UIInfo>, for a given language
         */
        $this->testLocalizedElements($privacyStatementURL);

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
     * Add the value to the elements-property
     *
     * @param \SimpleSAML\XML\Chunk $child
     */
    public function addChild(Chunk $child): void
    {
        $this->elements[] = $child;
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
            && empty($this->elements)
        );
    }


    /**
     * Test localized elements for multiple items with the same language
     *
     * @param (\SimpleSAML\SAML2\XML\md\AbstractLocalizedURL|
     *         \SimpleSAML\SAML2\XML\md\AbstractLocalizedName|
     *         \SimpleSAML\XML\SAML2\mdui\Keywords)[] $items
     * @return void
     */
    private function testLocalizedElements(array $elements) {
        if (!empty($elements)) {
            $types = array_map('get_class', $elements);
            Assert::maxCount(array_unique($types), 1, 'Multiple class types cannot be used.');

            $languages = array_map(
                function ($elt) {
                    return $elt->getLanguage();
                },
                $elements
            );
            Assert::uniqueValues(
                $languages,
                'There MUST NOT be more than one <' . $elements[0]->getQualifiedName() . '>,'
                . ' within a given <mdui:UIInfo>, for a given language',
                ProtocolViolationException::class
            );
        }
    }


    /**
     * Convert XML into a UIInfo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
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
        foreach (XPath::xpQuery($xml, './*', XPath::getXPath($xml)) as $node) {
            if ($node->namespaceURI !== UIInfo::NS) {
                $children[] = new Chunk($node);
            }
        }

        return new static(
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

        foreach ($this->getDisplayName() as $child) {
            $child->toXML($e);
        }

        foreach ($this->getDescription() as $child) {
            $child->toXML($e);
        }

        foreach ($this->getInformationURL() as $child) {
            $child->toXML($e);
        }

        foreach ($this->getPrivacyStatementURL() as $child) {
            $child->toXML($e);
        }

        foreach ($this->getKeywords() as $child) {
            $child->toXML($e);
        }

        foreach ($this->getLogo() as $child) {
            $child->toXML($e);
        }

        foreach ($this->getElements() as $child) {
            $child->toXML($e);
        }

        return $e;
    }


    /**
     * Create a class from an array
     *
     * NOTE: this method does not support passing additional child-objects
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): static
    {
        $DisplayName = [];
        if (!empty($data['DisplayName'])) {
            foreach ($data['DisplayName'] as $l => $k) {
                $DisplayName[] = DisplayName::fromArray([$l => $k]);
            }
        }

        $Description = [];
        if (!empty($data['Description'])) {
            foreach ($data['Description'] as $l => $k) {
                $Description[] = Description::fromArray([$l => $k]);
            }
        }

        $InformationURL = [];
        if (!empty($data['InformationURL'])) {
            foreach ($data['InformationURL'] as $l => $k) {
                $InformationURL[] = InformationURL::fromArray([$l => $k]);
            }
        }

        $PrivacyStatementURL = [];
        if (!empty($data['PrivacyStatementURL'])) {
            foreach ($data['PrivacyStatementURL'] as $l => $k) {
                $PrivacyStatementURL[] = PrivacyStatementURL::fromArray([$l => $k]);
            }
        }

        $Keywords = [];
        if (!empty($data['Keywords'])) {
            foreach ($data['Keywords'] as $l => $k) {
                $Keywords[] = Keywords::fromArray([$l => $k]);
            }
        }

        $Logo = [];
        if (!empty($data['Logo'])) {
            foreach ($data['Logo'] as $l) {
                $Logo[] = Logo::fromArray($l);
            }
        }

        return new static(
            $DisplayName,
            $Description,
            $InformationURL,
            $PrivacyStatementURL,
            $Keywords,
            $Logo
        );
    }


    /**
     * Create an array from this class
     *
     * NOTE: this method does not support passing additional child-objects
     *
     * @return array
     */
    public function toArray(): array
    {
        $displayName = [];
        foreach ($this->DisplayName as $child) {
            $displayName = array_merge($displayName, $child->toArray());
        }

        $description = [];
        foreach ($this->Description as $child) {
            $description = array_merge($description, $child->toArray());
        }

        $infoUrl = [];
        foreach ($this->InformationURL as $child) {
            $infoUrl = array_merge($infoUrl, $child->toArray());
        }

        $privacyUrl = [];
        foreach ($this->PrivacyStatementURL as $child) {
            $privacyUrl = array_merge($privacyUrl, $child->toArray());
        }

        $keywords = [];
        foreach ($this->Keywords as $child) {
            $keywords = array_merge($keywords, $child->toArray());
        }

        $logo = [];
        foreach ($this->Logo as $child) {
            $logo[] = $child->toArray();
        }

        return [
            'DisplayName' => $displayName,
            'Description' => $description,
            'InformationURL' => $infoUrl,
            'PrivacyStatementURL' => $privacyUrl,
            'Keywords' => $keywords,
            'Logo' => $logo
        ];
    }
}
