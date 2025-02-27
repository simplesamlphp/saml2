<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\TypedTextContentTrait;

use function strval;

/**
 * Class representing SAML2 Action
 *
 * @package simplesamlphp/saml2
 */
final class Action extends AbstractSamlElement
{
    use TypedTextContentTrait;

    /** @var string */
    public const TEXTCONTENT_TYPE = SAMLStringValue::class;


    /**
     * Initialize an Action.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $namespace
     *   NOTE: This attribute was marked REQUIRED in the 2012 SAML errata (E36)
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue $content
     */
    public function __construct(
        protected SAMLAnyURIValue $namespace,
        SAMLStringValue $content,
    ) {
        $this->setContent($content);
    }


    /**
     * Collect the value of the namespace-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue
     */
    public function getNamespace(): SAMLAnyURIValue
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
            self::getAttribute($xml, 'Namespace', SAMLAnyURIValue::class),
            SAMLStringValue::fromString($xml->textContent),
        );
    }


    /**
     * Convert this Action to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Action to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('Namespace', strval($this->getNamespace()));
        $e->textContent = strval($this->getContent());

        return $e;
    }
}
