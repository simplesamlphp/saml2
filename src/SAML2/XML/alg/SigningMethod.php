<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\alg;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\Exception\MissingAttributeException;

/**
 * Class for handling the alg:SigningMethod element.
 *
 * @link http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-algsupport.pdf
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
final class SigningMethod extends AbstractAlgElement
{
    /**
     * An URI identifying the algorithm supported for XML signature operations.
     *
     * @var string
     */
    protected $Algorithm;

    /**
     * The smallest key size, in bits, that the entity supports in conjunction with the algorithm. If omitted, no
     * minimum is implied.
     *
     * @var int|null
     */
    protected $MinKeySize = null;

    /**
     * The largest key size, in bits, that the entity supports in conjunction with the algorithm. If omitted, no
     * maximum is implied.
     *
     * @var int|null
     */
    protected $MaxKeySize = null;


    /**
     * Create/parse an alg:SigningMethod element.
     *
     * @param string $Algorithm
     * @param int|null $MinKeySize
     * @param int|null $MaxKeySize
     */
    public function __construct(string $Algorithm, ?int $MinKeySize = null, ?int $MaxKeySize = null)
    {
        $this->setAlgorithm($Algorithm);
        $this->setMinKeySize($MinKeySize);
        $this->setMaxKeySize($MaxKeySize);
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
     * @return void
     */
    private function setAlgorithm(string $algorithm): void
    {
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
     * @return void
     */
    private function setMinKeySize(int $minKeySize = null): void
    {
        Assert::nullOrNatural($minKeySize);
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
     * @return void
     */
    private function setMaxKeySize(int $maxKeySize = null): void
    {
        $this->MaxKeySize = $maxKeySize;
    }


    /**
     * Convert XML into a SigningMethod
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\SAML2\Exception\MissingAttributeException if the supplied argument is missing the Algorithm attribute
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'SigningMethod', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SigningMethod::NS, InvalidDOMElementException::class);

        $Algorithm = self::getAttribute($xml, 'Algorithm');
        $MinKeySize = self::getIntegerAttribute($xml, 'MinKeySize', null);
        $MaxKeySize = self::getIntegerAttribute($xml, 'MaxKeySize', null);

        return new self($Algorithm, $MinKeySize, $MaxKeySize);
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

        return $e;
    }
}
