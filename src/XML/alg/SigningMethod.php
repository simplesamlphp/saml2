<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\alg;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\Type\PositiveIntegerValue;
use SimpleSAML\XML\XsNamespace as NS;

/**
 * Class for handling the alg:SigningMethod element.
 *
 * @link http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-algsupport.pdf
 * @package simplesamlphp/saml2
 */
final class SigningMethod extends AbstractAlgElement implements SchemaValidatableElementInterface
{
    use ExtendableElementTrait;
    use SchemaValidatableElementTrait;


    /** The namespace-attribute for the xs:any element */
    public const XS_ANY_ELT_NAMESPACE = NS::ANY;


    /**
     * Create/parse an alg:SigningMethod element.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $algorithm
     * @param \SimpleSAML\XML\Type\PositiveIntegerValue|null $minKeySize
     * @param \SimpleSAML\XML\Type\PositiveIntegerValue|null $maxKeySize
     * @param \SimpleSAML\XML\Chunk[] $elements
     */
    public function __construct(
        protected SAMLAnyURIValue $algorithm,
        protected ?PositiveIntegerValue $minKeySize = null,
        protected ?PositiveIntegerValue $maxKeySize = null,
        array $elements = [],
    ) {
        $this->setElements($elements);
    }


    /**
     * Collect the value of the Algorithm-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue
     */
    public function getAlgorithm(): SAMLAnyURIValue
    {
        return $this->algorithm;
    }


    /**
     * Collect the value of the MinKeySize-property
     *
     * @return \SimpleSAML\XML\Type\PositiveIntegerValue|null
     */
    public function getMinKeySize(): ?PositiveIntegerValue
    {
        return $this->minKeySize;
    }


    /**
     * Collect the value of the MaxKeySize-property
     *
     * @return \SimpleSAML\XML\Type\PositiveIntegerValue|null
     */
    public function getMaxKeySize(): ?PositiveIntegerValue
    {
        return $this->maxKeySize;
    }


    /**
     * Convert XML into a SigningMethod
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied argument is missing the Algorithm attribute
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'SigningMethod', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SigningMethod::NS, InvalidDOMElementException::class);

        return new static(
            self::getAttribute($xml, 'Algorithm', SAMLAnyURIValue::class),
            self::getOptionalAttribute($xml, 'MinKeySize', PositiveIntegerValue::class, null),
            self::getOptionalAttribute($xml, 'MaxKeySize', PositiveIntegerValue::class, null),
            self::getChildElementsFromXML($xml),
        );
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('Algorithm', $this->getAlgorithm()->getValue());

        if ($this->getMinKeySize() !== null) {
            $e->setAttribute('MinKeySize', $this->getMinKeySize()->getValue());
        }

        if ($this->getMaxKeySize() !== null) {
            $e->setAttribute('MaxKeySize', $this->getMaxKeySize()->getValue());
        }

        /** @var \SimpleSAML\XML\SerializableElementInterface $element */
        foreach ($this->getElements() as $element) {
            $element->toXML($e);
        }

        return $e;
    }
}
