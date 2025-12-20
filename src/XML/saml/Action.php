<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\TypedTextContentTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;

use function strval;

/**
 * Class representing SAML2 Action
 *
 * @package simplesamlphp/saml2
 */
final class Action extends AbstractSamlElement
{
    use TypedTextContentTrait;


    public const string TEXTCONTENT_TYPE = SAMLStringValue::class;


    /**
     * Initialize an Action.
     *
     * NOTE: The namespace-attribute was marked REQUIRED in the 2012 SAML errata (E36)
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $namespace
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
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
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
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('Namespace', strval($this->getNamespace()));
        $e->textContent = strval($this->getContent());

        return $e;
    }
}
