<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ds;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\Utils;

/**
 * Class representing a ds:KeyName element.
 *
 * @package simplesamlphp/saml2
 */
final class KeyName extends AbstractDsElement
{
    /**
     * The key name.
     *
     * @var string
     */
    protected string $name;


    /**
     * Initialize a KeyName element.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }


    /**
     * Collect the value of the name-property
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Set the value of the name-property
     *
     * @param string $name
     * @return void
     */
    private function setName(string $name): void
    {
        $this->name = $name;
    }


    /**
     * Convert XML into a KeyName
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'KeyName', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, KeyName::NS, InvalidDOMElementException::class);

        return new self($xml->textContent);
    }


    /**
     * Convert this KeyName element to XML.
     *
     * @param \DOMElement|null $parent The element we should append this KeyName element to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->name;

        return $e;
    }
}
