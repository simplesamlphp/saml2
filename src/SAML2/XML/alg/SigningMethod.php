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
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Exception\InvalidArgumentException;

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
    public const NAMESPACE = C::XS_ANY_NS_ANY;

    /**
     * An URI identifying the algorithm supported for XML signature operations.
     *
     * @var string
     */
    protected string $Algorithm;

    /**
     * The smallest key size, in bits, that the entity supports in conjunction with the algorithm. If omitted, no
     * minimum is implied.
     *
     * @var int|null
     */
    protected ?int $MinKeySize = null;

    /**
     * The largest key size, in bits, that the entity supports in conjunction with the algorithm. If omitted, no
     * maximum is implied.
     *
     * @var int|null
     */
    protected ?int $MaxKeySize = null;


    /**
     * Create/parse an alg:SigningMethod element.
     *
     * @param string $Algorithm
     * @param int|null $MinKeySize
     * @param int|null $MaxKeySize
     * @param \SimpleSAML\XML\Chunk[] $elements
     */
    public function __construct(string $Algorithm, ?int $MinKeySize = null, ?int $MaxKeySize = null, array $elements = [])
    {
        $this->setAlgorithm($Algorithm);
        $this->setMinKeySize($MinKeySize);
        $this->setMaxKeySize($MaxKeySize);
        $this->setElements($elements);
    }


    /**
     * Collect the value of the Algorithm-property
     *
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->Algorithm;
    }


    /**
     * Set the value of the Algorithm-property
     *
     * @param string $algorithm
     */
    private function setAlgorithm(string $algorithm): void
    {
        Assert::validURI($algorithm, SchemaViolationException::class); // Covers the empty string
        Assert::oneOf(
            $algorithm,
            array_merge(array_keys(C::$RSA_DIGESTS), array_keys(C::$HMAC_DIGESTS)),
            'Invalid signature method',
            InvalidArgumentException::class
        );
        $this->Algorithm = $algorithm;
    }


    /**
     * Collect the value of the MinKeySize-property
     *
     * @return int|null
     */
    public function getMinKeySize(): ?int
    {
        return $this->MinKeySize;
    }


    /**
     * Set the value of the MinKeySize-property
     *
     * @param int|null $minKeySize
     */
    private function setMinKeySize(?int $minKeySize): void
    {
        Assert::nullOrPositiveInteger($minKeySize);
        $this->MinKeySize = $minKeySize;
    }


    /**
     * Collect the value of the MaxKeySize-property
     *
     * @return int|null
     */
    public function getMaxKeySize(): ?int
    {
        return $this->MaxKeySize;
    }


    /**
     * Set the value of the MaxKeySize-property
     *
     * @param int|null $maxKeySize
     */
    private function setMaxKeySize(?int $maxKeySize): void
    {
        Assert::nullOrPositiveInteger($maxKeySize);
        $this->MaxKeySize = $maxKeySize;
    }


    /**
     * Convert XML into a SigningMethod
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied argument is missing the Algorithm attribute
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'SigningMethod', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SigningMethod::NS, InvalidDOMElementException::class);

        $Algorithm = self::getAttribute($xml, 'Algorithm');
        $MinKeySize = self::getIntegerAttribute($xml, 'MinKeySize', null);
        $MaxKeySize = self::getIntegerAttribute($xml, 'MaxKeySize', null);

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

        $e->setAttribute('Algorithm', $this->Algorithm);

        if ($this->MinKeySize !== null) {
            $e->setAttribute('MinKeySize', strval($this->MinKeySize));
        }

        if ($this->MaxKeySize !== null) {
            $e->setAttribute('MaxKeySize', strval($this->MaxKeySize));
        }

        foreach ($this->elements as $element) {
            $e->appendChild($e->ownerDocument->importNode($element->getXML(), true));
        }

        return $e;
    }
}
