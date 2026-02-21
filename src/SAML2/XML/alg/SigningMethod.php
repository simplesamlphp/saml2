<?php

declare(strict_types=1);

namespace SAML2\XML\alg;

use DOMElement;
use Exception;
use Webmozart\Assert\Assert;

/**
 * Class for handling the alg:SigningMethod element.
 *
 * @link http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-algsupport.pdf
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
class SigningMethod
{
    /**
     * An URI identifying the algorithm supported for XML signature operations.
     *
     * @var string
     */
    private $Algorithm = '';

    /**
     * The smallest key size, in bits, that the entity supports in conjunction with the algorithm. If omitted, no
     * minimum is implied.
     *
     * @var int|null
     */
    private $MinKeySize = null;

    /**
     * The largest key size, in bits, that the entity supports in conjunction with the algorithm. If omitted, no
     * maximum is implied.
     *
     * @var int|null
     */
    private $MaxKeySize = null;


    /**
     * Create/parse an alg:SigningMethod element.
     *
     * @param \DOMElement|null $xml The XML element we should load or null to create a new one from scratch.
     *
     * @throws \Exception
     */
    public function __construct(?DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('Algorithm')) {
            throw new Exception('Missing required attribute "Algorithm" in alg:SigningMethod element.');
        }
        $this->Algorithm = $xml->getAttribute('Algorithm');

        if ($xml->hasAttribute('MinKeySize')) {
            $this->MinKeySize = intval($xml->getAttribute('MinKeySize'));
        }

        if ($xml->hasAttribute('MaxKeySize')) {
            $this->MaxKeySize = intval($xml->getAttribute('MaxKeySize'));
        }
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
    public function setAlgorithm(string $algorithm): void
    {
        $this->Algorithm = $algorithm;
    }


    /**
     * Collect the value of the MinKeySize-property
     */
    public function getMinKeySize(): ?int
    {
        return $this->MinKeySize;
    }


    /**
     * Set the value of the MinKeySize-property
     */
    public function setMinKeySize(?int $minKeySize = null): void
    {
        $this->MinKeySize = $minKeySize;
    }


    /**
     * Collect the value of the MaxKeySize-property
     */
    public function getMaxKeySize(): ?int
    {
        return $this->MaxKeySize;
    }


    /**
     * Set the value of the MaxKeySize-property
     */
    public function setMaxKeySize(?int $maxKeySize = null): void
    {
        $this->MaxKeySize = $maxKeySize;
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement $parent The element we should append to.
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->Algorithm, 'Cannot convert SigningMethod to XML without an Algorithm set.');
        Assert::nullOrInteger($this->MinKeySize);
        Assert::nullOrInteger($this->MaxKeySize);

        $doc = $parent->ownerDocument;
        $e = $doc->createElementNS(Common::NS, 'alg:SigningMethod');
        $parent->appendChild($e);
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
