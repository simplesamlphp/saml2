<?php

namespace SAML2\XML\alg;

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
    public $Algorithm;


    /**
     * The smallest key size, in bits, that the entity supports in conjunction with the algorithm. If omitted, no
     * minimum is implied.
     *
     * @var int|null
     */
    public $MinKeySize;


    /**
     * The largest key size, in bits, that the entity supports in conjunction with the algorithm. If omitted, no
     * maximum is implied.
     *
     * @var int|null
     */
    public $MaxKeySize;


    /**
     * Collect the value of the Algorithm-property
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->Algorithm;
    }

    /**
     * Set the value of the Algorithm-property
     * @param string $algorithm
     */
    public function setAlgorithm($algorithm)
    {
        assert(is_string($algorithm));
        $this->Algorithm = $algorithm;
    }


    /**
     * Collect the value of the MinKeySize-property
     * @return int|null
     */
    public function getMinKeySize()
    {
        return $this->MinKeySize;
    }

    /**
     * Set the value of the MinKeySize-property
     * @param int|null $minKeySize
     */
    public function setMinKeySize($minKeySize = null)
    {
        assert(is_int($minKeySize) || is_null($minKeySize));
        $this->MinKeySize = $minKeySize;
    }

    /**
     * Collect the value of the MaxKeySize-property
     * @return int|null
     */
    public function getMaxKeySize()
    {
        return $this->MaxKeySize;
    }

    /**
     * Set the value of the MaxKeySize-property
     * @param int|null $maxKeySize
     */
    public function setMaxKeySize($maxKeySize = null)
    {
        assert(is_int($maxKeySize) || is_null($maxKeySize));
        $this->MaxKeySize = $maxKeySize;
    }

    /**
     * Create/parse an alg:SigningMethod element.
     *
     * @param \DOMElement|null $xml The XML element we should load or null to create a new one from scratch.
     *
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('Algorithm')) {
            throw new \Exception('Missing required attribute "Algorithm" in alg:SigningMethod element.');
        }
        $this->setAlgorithm($xml->getAttribute('Algorithm'));

        if ($xml->hasAttribute('MinKeySize')) {
            $this->setMinKeySize(intval($xml->getAttribute('MinKeySize')));
        }

        if ($xml->hasAttribute('MaxKeySize')) {
            $this->setMaxKeySize(intval($xml->getAttribute('MaxKeySize')));
        }
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert(is_string($this->getAlgorithm()));
        assert(is_int($this->getMinKeySize()) || is_null($this->getMinKeySize()));
        assert(is_int($this->getMaxKeySize()) || is_null($this->getMaxKeySize()));

        $doc = $parent->ownerDocument;
        $e = $doc->createElementNS(Common::NS, 'alg:SigningMethod');
        $parent->appendChild($e);
        $e->setAttribute('Algorithm', $this->getAlgorithm());

        if ($this->getMinKeySize() !== null) {
            $e->setAttribute('MinKeySize', $this->getMinKeySize());
        }

        if ($this->getMaxKeySize() !== null) {
            $e->setAttribute('MaxKeySize', $this->getMaxKeySize());
        }

        return $e;
    }
}
