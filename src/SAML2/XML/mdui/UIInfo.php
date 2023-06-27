<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdui;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XML\SerializableElementInterface;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;

/**
 * Class for handling the metadata extensions for login and discovery user interface
 *
 * @link: http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-ui/v1.0/sstc-saml-metadata-ui-v1.0.pdf
 * @package simplesamlphp/saml2
 */
final class UIInfo extends AbstractMduiElement implements ArrayizableElementInterface
{
    use ExtendableElementTrait;

    /** The namespace-attribute for the xs:any element */
    public const XS_ANY_ELT_NAMESPACE = C::XS_ANY_NS_OTHER;

    /**
     * Create a UIInfo element.
     *
     * @param \SimpleSAML\SAML2\XML\mdui\DisplayName[] $displayName
     * @param \SimpleSAML\SAML2\XML\mdui\Description[] $description
     * @param \SimpleSAML\SAML2\XML\mdui\InformationURL[] $informationURL
     * @param \SimpleSAML\SAML2\XML\mdui\PrivacyStatementURL[] $privacyStatementURL
     * @param \SimpleSAML\SAML2\XML\mdui\Keywords[] $keywords
     * @param \SimpleSAML\SAML2\XML\mdui\Logo[] $logo
     * @param \SimpleSAML\XML\Chunk[] $children
     */
    public function __construct(
        protected array $displayName = [],
        protected array $description = [],
        protected array $informationURL = [],
        protected array $privacyStatementURL = [],
        protected array $keywords = [],
        protected array $logo = [],
        array $children = [],
    ) {
        Assert::maxCount($displayName, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($displayName, DisplayName::class);
        /**
         * 2.1.2:  There MUST NOT be more than one <mdui:DisplayName>,
         *         within a given <mdui:UIInfo>, for a given language
         */
        $this->testLocalizedElements($displayName);

        Assert::maxCount($description, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($description, Description::class);
        /**
         * 2.1.3:  There MUST NOT be more than one <mdui:Description>,
         *         within a given <mdui:UIInfo>, for a given language
         */
        $this->testLocalizedElements($description);

        Assert::maxCount($keywords, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($keywords, Keywords::class);
        /**
         * 2.1.4:  There MUST NOT be more than one <mdui:Keywords>,
         *         within a given <mdui:UIInfo>, for a given language
         */
        $this->testLocalizedElements($keywords);

        Assert::maxCount($informationURL, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($informationURL, InformationURL::class);
        /**
         * 2.1.6:  There MUST NOT be more than one <mdui:InformationURL>,
         *         within a given <mdui:UIInfo>, for a given language
         */
        $this->testLocalizedElements($informationURL);

        Assert::maxCount($privacyStatementURL, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($privacyStatementURL, PrivacyStatementURL::class);
        /**
         * 2.1.7:  There MUST NOT be more than one <mdui:PrivacyStatementURL>,
         *         within a given <mdui:UIInfo>, for a given language
         */
        $this->testLocalizedElements($privacyStatementURL);

        Assert::maxCount($logo, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($logo, Logo::class);

        $this->setElements($children);
    }


    /**
     * Collect the value of the Keywords-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\Keywords[]
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }


    /**
     * Add the value to the Keywords-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\Keywords $keyword
     */
    public function addKeyword(Keywords $keyword): void
    {
        /**
         * 2.1.4:  There MUST NOT be more than one <mdui:Keywords>,
         *         within a given <mdui:UIInfo>, for a given language
         */
        $keywords = array_merge($this->keywords, [$keyword]);
        $this->testLocalizedElements($keywords);
        $this->keywords = $keywords;
    }


    /**
     * Collect the value of the DisplayName-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\DisplayName[]
     */
    public function getDisplayName(): array
    {
        return $this->displayName;
    }


    /**
     * Collect the value of the Description-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\Description[]
     */
    public function getDescription(): array
    {
        return $this->description;
    }


    /**
     * Collect the value of the InformationURL-property
     * @return \SimpleSAML\SAML2\XML\mdui\InformationURL[]
     */
    public function getInformationURL(): array
    {
        return $this->informationURL;
    }


    /**
     * Collect the value of the PrivacyStatementURL-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\PrivacyStatementURL[]
     */
    public function getPrivacyStatementURL(): array
    {
        return $this->privacyStatementURL;
    }


    /**
     * Collect the value of the Logo-property
     *
     * @return \SimpleSAML\SAML2\XML\mdui\Logo[]
     */
    public function getLogo(): array
    {
        return $this->logo;
    }


    /**
     * Add the value to the Logo-property
     *
     * @param \SimpleSAML\SAML2\XML\mdui\Logo $logo
     */
    public function addLogo(Logo $logo): void
    {
        $this->logo[] = $logo;
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
        return empty($this->displayName)
            && empty($this->description)
            && empty($this->informationURL)
            && empty($this->privacyStatementURL)
            && empty($this->keywords)
            && empty($this->logo)
            && empty($this->elements);
    }


    /**
     * Test localized elements for multiple items with the same language
     *
     * @param (\SimpleSAML\SAML2\XML\md\AbstractLocalizedURL|
     *         \SimpleSAML\SAML2\XML\md\AbstractLocalizedName|
     *         \SimpleSAML\SAML2\XML\mdui\Keywords)[] $items
     * @return void
     */
    private function testLocalizedElements(array $elements)
    {
        if (!empty($elements)) {
            $types = array_map('get_class', $elements);
            Assert::maxCount(array_unique($types), 1, 'Multiple class types cannot be used.');

            $languages = array_map(
                function ($elt) {
                    return $elt->getLanguage();
                },
                $elements,
            );
            Assert::uniqueValues(
                $languages,
                'There MUST NOT be more than one <' . $elements[0]->getQualifiedName() . '>,'
                . ' within a given <mdui:UIInfo>, for a given language',
                ProtocolViolationException::class,
            );
        }
    }


    /**
     * Convert XML into a UIInfo
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
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
            $children,
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

        /** @var \SimpleSAML\XML\SerializableElementInterface $child */
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
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $data = self::processArrayContents($data);

        return new static(
            $data['DisplayName'] ?? [],
            $data['Description'] ?? [],
            $data['InformationURL'] ?? [],
            $data['PrivacyStatementURL'] ?? [],
            $data['Keywords'] ?? [],
            $data['Logo'] ?? [],
            $data['children'] ?? [],
        );
    }


    /**
     * Validates an array representation of this object and returns the same array with
     * rationalized keys (casing) and parsed sub-elements.
     *
     * @param array $data
     * @return array $data
     */
    private static function processArrayContents(array $data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);

        // Make sure the array keys are known for this kind of object
        Assert::allOneOf(
            array_keys($data),
            [
                'displayname',
                'description',
                'informationurl',
                'privacystatementurl',
                'keywords',
                'logo',
                'children',
            ],
            ArrayValidationException::class,
        );

        $retval = [];

        if (array_key_exists('displayname', $data)) {
            foreach ($data['displayname'] as $l => $displayName) {
                $retval['DisplayName'][] = DisplayName::fromArray([$l => $displayName]);
            }
        }

        if (array_key_exists('description', $data)) {
            foreach ($data['description'] as $l => $description) {
                $retval['Description'][] = Description::fromArray([$l => $description]);
            }
        }

        if (array_key_exists('informationurl', $data)) {
            foreach ($data['informationurl'] as $l => $iu) {
                $retval['InformationURL'][] = InformationURL::fromArray([$l => $iu]);
            }
        }

        if (array_key_exists('privacystatementurl', $data)) {
            foreach ($data['privacystatementurl'] as $l => $psu) {
                $retval['PrivacyStatementURL'][] = PrivacyStatementURL::fromArray([$l => $psu]);
            }
        }

        if (array_key_exists('keywords', $data)) {
            foreach ($data['keywords'] as $l => $keywords) {
                $retval['Keywords'][] = Keywords::fromArray([$l => $keywords]);
            }
        }

        if (array_key_exists('logo', $data)) {
            foreach ($data['logo'] as $logo) {
                $retval['Logo'][] = Logo::fromArray($logo);
            }
        }

        if (array_key_exists('children', $data)) {
            Assert::isArray($data['children'], ArrayValidationException::class);
            Assert::allIsInstanceOf(
                $data['children'],
                SerializableElementInterface::class,
                ArrayValidationException::class,
            );
            $retval['children'] = $data['children'];
        }

        return array_filter($retval);
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
        foreach ($this->getDisplayName() as $child) {
            $displayName = array_merge($displayName, $child->toArray());
        }

        $description = [];
        foreach ($this->getDescription() as $child) {
            $description = array_merge($description, $child->toArray());
        }

        $infoUrl = [];
        foreach ($this->getInformationURL() as $child) {
            $infoUrl = array_merge($infoUrl, $child->toArray());
        }

        $privacyUrl = [];
        foreach ($this->getPrivacyStatementURL() as $child) {
            $privacyUrl = array_merge($privacyUrl, $child->toArray());
        }

        $keywords = [];
        foreach ($this->getKeywords() as $child) {
            $keywords = array_merge($keywords, $child->toArray());
        }

        $logo = [];
        foreach ($this->getLogo() as $child) {
            $logo[] = $child->toArray();
        }

        $children = $this->getElements();

        return [] +
            (empty($displayName) ? [] : ['DisplayName' => $displayName]) +
            (empty($description) ? [] : ['Description' => $description]) +
            (empty($infoUrl) ? [] : ['InformationURL' => $infoUrl]) +
            (empty($privacyUrl) ? [] : ['PrivacyStatementURL' => $privacyUrl]) +
            (empty($keywords) ? [] : ['Keywords' => $keywords]) +
            (empty($logo) ? [] : ['Logo' => $logo]) +
            (empty($children) ? [] : ['children' => $children]);
    }
}
