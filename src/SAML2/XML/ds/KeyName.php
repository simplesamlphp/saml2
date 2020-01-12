<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use DOMElement;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing a ds:KeyName element.
 *
 * @package SimpleSAMLphp
 */
final class KeyName extends AbstractDsElement
{
    /**
     * The key name.
     *
     * @var string
     */
    protected $name;


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
     */
    public static function fromXML(DOMElement $xml): object
    {
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
//        $e = $this->instantiateParentElement($parent);
        return Utils::addString($parent, self::NS, 'ds:KeyName', $this->name);
    }
}
