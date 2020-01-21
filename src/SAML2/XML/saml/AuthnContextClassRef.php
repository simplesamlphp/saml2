<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML2 AuthnContextClassRef
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
final class AuthnContextClassRef extends AbstractSamlElement
{
    /** @var string */
    protected $classRef;


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
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getClassRef(): string
    {
        return $this->classRef;
    }


    /**
     * Set the value of the classRef-property
     *
     * @param string $name
     * @return void
     */
    private function setClassRef(string $classRef): void
    {
        $this->classRef = trim($classRef);
    }


    /**
     * Convert XML into a AuthnContextClassRef
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\AuthnContextClassRef
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnContextClassRef');
        Assert::same($xml->namespaceURI, Constants::NS_SAML);

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
