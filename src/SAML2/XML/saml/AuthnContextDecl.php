<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use DOMNodeList;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\Utils;

/**
 * Class representing SAML2 AuthnContextDecl
 *
 * @package simplesamlphp/saml2
 */
final class AuthnContextDecl extends AbstractSamlElement
{
    /** @var \DOMNodeList */
    protected DOMNodeList $decl;


    /**
     * Initialize an AuthnContextDecl.
     *
     * @param \DOMNodeList $decl
     */
    public function __construct(DOMNodeList $decl)
    {
        $this->setDecl($decl);
    }


    /**
     * Collect the value of the decl-property
     *
     * @return \DOMNodeList
     */
    public function getDecl(): DOMNodeList
    {
        return $this->decl;
    }


    /**
     * Set the value of the decl-property
     *
     * @param \DOMNodeList $decl
     */
    private function setDecl(DOMNodeList $decl): void
    {
        $this->decl = $decl;
    }


    /**
     * Convert XML into a AuthnContextDecl
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\AuthnContextDecl
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AuthnContextDecl', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnContextDecl::NS, InvalidDOMElementException::class);

        return new static($xml->childNodes);
    }


    /**
     * Convert this AuthContextDecl to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthnContextDecl to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getDecl() as $node) {
            $e->appendChild($e->ownerDocument->importNode($node, true));
        }

        return $e;
    }
}
