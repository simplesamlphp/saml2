<?php

declare(strict_types=1);

namespace SAML2\XML\alg;

use DOMElement;
use SAML2\DOMDocumentFactory;
use SAML2\Exception\InvalidDOMElementException;
use SimpleSAML\Assert\Assert;

/**
 * Class for handling the alg:DigestMethod element.
 *
 * @link http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-algsupport.pdf
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
final class DigestMethod extends AbstractAlgElement
{
    /**
     * An URI identifying an algorithm supported for digest operations.
     *
     * @var string
     */
    protected $Algorithm;


    /**
     * Create/parse an alg:DigestMethod element.
     *
     * @param string $Algorithm
     */
    public function __construct(string $Algorithm)
    {
        $this->setAlgorithm($Algorithm);
    }


    /**
     * Collect the value of the algorithm-property
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
     * Convert XML into a DigestMethod
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SAML2\Exception\MissingAttributeException if the mandatory Algorithm-attribute is missing
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'DigestMethod', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, DigestMethod::NS, InvalidDOMElementException::class);

        $Algorithm = self::getAttribute($xml, 'Algorithm');

        return new self($Algorithm);
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

        return $e;
    }
}
