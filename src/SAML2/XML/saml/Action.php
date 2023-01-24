<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\StringElementTrait;

/**
 * Class representing SAML2 Action
 *
 * @package simplesamlphp/saml2
 */
final class Action extends AbstractSamlElement
{
    use StringElementTrait;


    /**
     * Initialize an Action.
     *
     * @param string $namespace  This attribute was marked REQUIRED in the 2012 SAML errata (E36)
     * @param string $content
     */
    public function __construct(
        protected string $namespace,
        string $content
    ) {
        Assert::validURI($namespace, SchemaViolationException::class); // Covers the empty string

        $this->setContent($content);
    }


    /**
     * Collect the value of the namespace-property
     *
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }


    /**
     * Convert XML into a Action
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Action', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Action::NS, InvalidDOMElementException::class);

        return new self(
            self::getAttribute($xml, 'Namespace', null),
            $xml->textContent,
        );
    }


    /**
     * Convert this Action to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Action to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('Namespace', $this->getNamespace());
        $e->textContent = $this->getContent();

        return $e;
    }
}
