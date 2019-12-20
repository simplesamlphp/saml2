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
 * @package SimpleSAMLphp
 */
class Scope
{
    /**
     * The namespace used for the Scope extension element.
     */
    public const NS = 'urn:mace:shibboleth:metadata:1.0';

    /**
     * The scope.
     *
     * @var string
     */
    private $scope;

    /**
     * Whether this is a regexp scope.
     *
     * @var bool
     */
    private $regexp = false;


    /**
     * Create a Scope.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $this->scope = $xml->textContent;
        $this->regexp = Utils::parseBoolean($xml, 'regexp', false);
    }


    /**
     * Collect the value of the scope-property
     *
     * @return string
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getScope(): string
    {
        Assert::notEmpty($this->scope);

        return $this->scope;
    }


    /**
     * Set the value of the scope-property
     *
     * @param string $scope
     * @return void
     */
    public function setScope(string $scope): void
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
    public function setIsRegexpScope(bool $regexp): void
    {
        $this->regexp = $regexp;
    }


    /**
     * Convert this Scope to XML.
     *
     * @param \DOMElement $parent The element we should append this Scope to.
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->scope);

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Scope::NS, 'shibmd:Scope');
        $parent->appendChild($e);

        $e->appendChild($doc->createTextNode($this->scope));

        if ($this->regexp === true) {
            $e->setAttribute('regexp', 'true');
        } else {
            $e->setAttribute('regexp', 'false');
        }

        return $e;
    }
}
