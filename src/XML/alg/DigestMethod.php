<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\alg;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\XML\Constants\NS;

/**
 * Class for handling the alg:DigestMethod element.
 *
 * @link http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-algsupport.pdf
 *
 * @package simplesamlphp/saml2
 */
final class DigestMethod extends AbstractAlgElement implements SchemaValidatableElementInterface
{
    use ExtendableElementTrait;
    use SchemaValidatableElementTrait;


    /** The namespace-attribute for the xs:any element */
    public const string XS_ANY_ELT_NAMESPACE = NS::ANY;


    /**
     * Create/parse an alg:DigestMethod element.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $algorithm
     * @param \SimpleSAML\XML\Chunk[] $elements
     */
    public function __construct(
        protected SAMLAnyURIValue $algorithm,
        array $elements = [],
    ) {
        $this->setElements($elements);
    }


    /**
     * Collect the value of the algorithm-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue
     */
    public function getAlgorithm(): SAMLAnyURIValue
    {
        return $this->algorithm;
    }


    /**
     * Convert XML into a DigestMethod
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the mandatory Algorithm-attribute is missing
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'DigestMethod', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, DigestMethod::NS, InvalidDOMElementException::class);

        return new static(
            self::getAttribute($xml, 'Algorithm', SAMLAnyURIValue::class),
            self::getChildElementsFromXML($xml),
        );
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Algorithm', $this->getAlgorithm()->getValue());

        foreach ($this->getElements() as $element) {
            /** @var \SimpleSAML\XML\SerializableElementInterface $element */
            $element->toXML($e);
        }

        return $e;
    }
}
