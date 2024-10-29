<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\shibmd;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\StringElementTrait;

/**
 * Class which represents the Scope element found in Shibboleth metadata.
 *
 * @link https://wiki.shibboleth.net/confluence/display/SC/ShibMetaExt+V1.0
 * @package simplesamlphp/saml2
 */
final class Scope extends AbstractShibmdElement
{
    use StringElementTrait;


    /**
     * Create a Scope.
     *
     * @param string $scope
     * @param bool|null $regexp
     */
    public function __construct(
        string $scope,
        protected ?bool $regexp = false,
    ) {
        $this->setContent($scope);
    }


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(string $content): void
    {
        Assert::notWhitespaceOnly($content);
    }


    /**
     * Collect the value of the regexp-property
     *
     * @return bool|null
     */
    public function isRegexpScope(): ?bool
    {
        return $this->regexp;
    }


    /**
     * Convert XML into a Scope
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Scope', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Scope::NS, InvalidDOMElementException::class);

        $scope = $xml->textContent;
        $regexp = self::getOptionalBooleanAttribute($xml, 'regexp', null);

        return new static($scope, $regexp);
    }


    /**
     * Convert this Scope to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Scope to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->getContent();

        if ($this->isRegexpScope() !== null) {
            $e->setAttribute('regexp', $this->isRegexpScope() ? 'true' : 'false');
        }

        return $e;
    }
}
