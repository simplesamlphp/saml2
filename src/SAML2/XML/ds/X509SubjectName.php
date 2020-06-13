<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use DOMElement;
use SAML2\Utils;
use SimpleSAML\Assert\Assert;

/**
 * Class representing a ds:X509SubjectName element.
 *
 * @package SimpleSAMLphp
 */
final class X509SubjectName extends AbstractDsElement
{
    /**
     * The subject name.
     *
     * @var string
     */
    protected $name;


    /**
     * Initialize a X509SubjectName element.
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
     * Convert XML into a X509SubjectName
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'X509SubjectName');
        Assert::same($xml->namespaceURI, X509SubjectName::NS);

        return new self($xml->textContent);
    }


    /**
     * Convert this X509SubjectName element to XML.
     *
     * @param \DOMElement|null $parent The element we should append this X509SubjectName element to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->name;

        return $e;
    }
}
