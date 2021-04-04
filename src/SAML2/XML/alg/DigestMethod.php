<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\alg;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSecurity\Constants;
use SimpleSAML\XMLSecurity\Exception\InvalidArgumentException;

/**
 * Class for handling the alg:DigestMethod element.
 *
 * @link http://docs.oasis-open.org/security/saml/Post2.0/sstc-saml-metadata-algsupport.pdf
 * @package simplesamlphp/saml2
 */
final class DigestMethod extends AbstractAlgElement
{
    /**
     * An URI identifying an algorithm supported for digest operations.
     *
     * @var string
     */
    protected string $Algorithm;


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
        Assert::oneOf(
            $algorithm,
            [
                Constants::DIGEST_SHA1,
                Constants::DIGEST_SHA224,
                Constants::DIGEST_SHA256,
                Constants::DIGEST_SHA384,
                Constants::DIGEST_SHA512,
                Constants::DIGEST_RIPEMD160,
            ],
            'Invalid digest method',
            InvalidArgumentException::class
        );

        $this->Algorithm = $algorithm;
    }


    /**
     * Convert XML into a DigestMethod
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the mandatory Algorithm-attribute is missing
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
