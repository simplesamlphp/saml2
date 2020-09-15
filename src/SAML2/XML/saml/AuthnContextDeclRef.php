<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Class representing SAML2 AuthnContextDeclRef
 *
 * @package simplesamlphp/saml2
 */
final class AuthnContextDeclRef extends AbstractSamlElement
{
    /** @var string */
    protected string $declRef;


    /**
     * Initialize an AuthnContextDeclRef.
     *
     * @param string $declRef
     */
    public function __construct(string $declRef)
    {
        $this->setDeclRef($declRef);
    }


    /**
     * Collect the value of the declRef-property
     *
     * @return string
     */
    public function getDeclRef(): string
    {
        return $this->declRef;
    }


    /**
     * Set the value of the declRef-property
     *
     * @param string $name
     */
    private function setDeclRef(string $declRef): void
    {
        $this->declRef = trim($declRef);
    }


    /**
     * Convert XML into a AuthnContextDeclRef
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnContextDeclRef', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnContextDeclRef::NS, InvalidDOMElementException::class);

        return new self($xml->textContent);
    }


    /**
     * Convert this AuthContextDeclRef to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthnContextDeclRef to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->declRef;

        return $e;
    }
}
