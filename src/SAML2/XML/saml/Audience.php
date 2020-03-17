<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use Webmozart\Assert\Assert;

/**
 * SAML Audience data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class Audience extends AbstractSamlElement
{
    /** @var string */
    protected $audience;


    /**
     * Initialize a saml:Audience
     *
     * @param string $audience
     */
    public function __construct(string $audience)
    {
        $this->setAudience($audience);
    }


    /**
     * Collect the audience
     *
     * @return string
     */
    public function getAudience(): string
    {
        return $this->audience;
    }


    /**
     * Set the value of the Audience-property
     *
     * @param string $audience
     * @return void
     */
    private function setAudience(string $audience): void
    {
        $this->audience = $audience;
    }


    /**
     * Convert XML into an Audience
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Audience');
        Assert::same($xml->namespaceURI, Audience::NS);

        return new self($xml->textContent);
    }


    /**
     * Convert this Audience to XML.
     *
     * @param \DOMElement|null $element The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Audience.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->audience;

        return $e;
    }
}
