<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\alg;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\ExtendableElementTrait;
use SimpleSAML\XML\XsNamespace as NS;

use function strval;

/**
 * Class for handling the alg:SigningMethod element.
 *
 * @link http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-algsupport.pdf
 * @package simplesamlphp/saml2
 */
final class SigningMethod extends AbstractAlgElement
{
    use ExtendableElementTrait;

    /** The namespace-attribute for the xs:any element */
    public const XS_ANY_ELT_NAMESPACE = NS::ANY;


    /**
     * Create/parse an alg:SigningMethod element.
     *
     * @param string $algorithm
     * @param int|null $minKeySize
     * @param int|null $maxKeySize
     * @param \SimpleSAML\XML\Chunk[] $elements
     */
    public function __construct(
        protected string $algorithm,
        protected ?int $minKeySize = null,
        protected ?int $maxKeySize = null,
        array $elements = [],
    ) {
        Assert::validURI($algorithm, SchemaViolationException::class); // Covers the empty string
        Assert::nullOrPositiveInteger($minKeySize);
        Assert::nullOrPositiveInteger($maxKeySize);

        $this->setElements($elements);
    }


    /**
     * Collect the value of the Algorithm-property
     *
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }


    /**
     * Collect the value of the MinKeySize-property
     *
     * @return int|null
     */
    public function getMinKeySize(): ?int
    {
        return $this->minKeySize;
    }


    /**
     * Collect the value of the MaxKeySize-property
     *
     * @return int|null
     */
    public function getMaxKeySize(): ?int
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

        $Algorithm = self::getAttribute($xml, 'Algorithm');
        $MinKeySize = self::getOptionalIntegerAttribute($xml, 'MinKeySize', null);
        $MaxKeySize = self::getOptionalIntegerAttribute($xml, 'MaxKeySize', null);

        $elements = [];
        foreach ($xml->childNodes as $element) {
            if (!($element instanceof DOMElement)) {
                continue;
            }

            $elements[] = new Chunk($element);
        }

        return new static($Algorithm, $MinKeySize, $MaxKeySize, $elements);
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $e->setAttribute('Algorithm', $this->getAlgorithm());

        if ($this->getMinKeySize() !== null) {
            $e->setAttribute('MinKeySize', strval($this->getMinKeySize()));
        }

        if ($this->getMaxKeySize() !== null) {
            $e->setAttribute('MaxKeySize', strval($this->getMaxKeySize()));
        }

        /** @var \SimpleSAML\XML\SerializableElementInterface $element */
        foreach ($this->getElements() as $element) {
            $element->toXML($e);
        }

        return $e;
    }
}
