<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

/**
 * Class representing SAML2 AuthnContextClassRef
 *
 * @package simplesamlphp/saml2
 */
final class AuthnContextClassRef extends AbstractSamlElement
{
    /** @var string */
    protected string $classRef;


    /**
     * Initialize an AuthnContextClassRef.
     *
     * @param string $classRef
     */
    public function __construct(string $classRef)
    {
        $this->setClassRef($classRef);
    }


    /**
     * Collect the value of the classRef-property
     *
     * @return string
     */
    public function getClassRef(): string
    {
        return $this->classRef;
    }


    /**
     * Set the value of the classRef-property
     *
     * @param string $name
     */
    private function setClassRef(string $classRef): void
    {
        $this->classRef = trim($classRef);
    }


    /**
     * Convert XML into a AuthnContextClassRef
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\saml\AuthnContextClassRef
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnContextClassRef', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnContextClassRef::NS, InvalidDOMElementException::class);

        return new self($xml->textContent);
    }


    /**
     * Convert this AuthContextClassRef to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthnContextClassRef to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->classRef;

        return $e;
    }
}
