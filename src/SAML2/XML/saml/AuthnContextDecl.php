<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use DOMNodeList;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Utils;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SAML2 AuthnContextDecl
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class AuthnContextDecl extends AbstractSamlElement
{
    /** @var \DOMNodeList */
    protected $decl;


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
     * @return void
     */
    private function setDecl(DOMNodeList $decl): void
    {
        $this->decl = $decl;
    }


    /**
     * Convert XML into a AuthnContextDecl
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\AuthnContextDecl
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnContextDecl', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnContextDecl::NS, InvalidDOMElementException::class);

        return new self($xml->childNodes);
    }


    /**
     * Convert this AuthContextDecl to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthnContextDecl to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->decl as $node) {
            $e->appendChild($e->ownerDocument->importNode($node, true));
        }

        return $e;
    }
}
