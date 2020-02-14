<?php

declare(strict_types=1);

namespace SAML2\XML\shibmd;

use DOMElement;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class which represents the Scope element found in Shibboleth metadata.
 *
 * @link https://wiki.shibboleth.net/confluence/display/SHIB/ShibbolethMetadataProfile
 * @package simplesamlphp/saml2
 */
final class Scope extends AbstractShibmdElement
{
    /**
     * The scope.
     *
     * @var string
     */
    protected $scope;

    /**
     * Whether this is a regexp scope.
     *
     * @var bool
     */
    protected $regexp;


    /**
     * Create a Scope.
     *
     * @param string $scope
     * @param bool $regexp
     */
    public function __construct(string $scope, bool $regexp = false)
    {
        $this->setScope($scope);
        $this->setIsRegexpScope($regexp);
    }


    /**
     * Collect the value of the scope-property
     *
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }


    /**
     * Set the value of the scope-property
     *
     * @param string $scope
     * @return void
     */
    private function setScope(string $scope): void
    {
        $this->scope = $scope;
    }


    /**
     * Collect the value of the regexp-property
     *
     * @return bool
     */
    public function isRegexpScope(): bool
    {
        return $this->regexp;
    }


    /**
     * Set the value of the regexp-property
     *
     * @param bool $regexp
     * @return void
     */
    private function setIsRegexpScope(bool $regexp): void
    {
        $this->regexp = $regexp;
    }


    /**
     * Convert XML into a NameIDPolicy
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Scope');
        Assert::same($xml->namespaceURI, Scope::NS);

        $scope = $xml->textContent;
        $regexp = Utils::parseBoolean($xml, 'regexp', false);

        return new self($scope, $regexp);
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
        $e->appendChild($e->ownerDocument->createTextNode($this->scope));

        if ($this->regexp === true) {
            $e->setAttribute('regexp', 'true');
        } else {
            $e->setAttribute('regexp', 'false');
        }

        return $e;
    }
}
