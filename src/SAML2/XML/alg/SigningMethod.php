<?php

declare(strict_types=1);

namespace SAML2\XML\alg;

use DOMElement;
use SAML2\DOMDocumentFactory;
use Webmozart\Assert\Assert;

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
     *
     * @throws \InvalidArgumentException if assertions are false
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
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'SigningMethod');
        Assert::same($xml->namespaceURI, SigningMethod::NS);

        if (!$xml->hasAttribute('Algorithm')) {
            throw new \Exception('Missing required attribute "Algorithm" in alg:SigningMethod element.');
        }

        $Algorithm = $xml->getAttribute('Algorithm');
        $MinKeySize = $xml->hasAttribute('MinKeySize') ? intval($xml->getAttribute('MinKeySize')) : null;
        $MaxKeySize = $xml->hasAttribute('MaxKeySize') ? intval($xml->getAttribute('MaxKeySize')) : null;

        return new self($Algorithm, $MinKeySize, $MaxKeySize);
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
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
