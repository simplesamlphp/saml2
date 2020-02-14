<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use DOMElement;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing a ds:X509Certificate element.
 *
 * @package simplesamlphp/saml2
 */
final class X509Certificate extends AbstractDsElement
{
    /**
     * The base64-encoded certificate.
     *
     * @var string
     */
    protected $certificate;


    /**
     * Initialize an X509Certificate element.
     *
     * @param string $certificate
     */
    public function __construct(string $certificate)
    {
        $this->setCertificate($certificate);
    }


    /**
     * Collect the value of the certificate-property
     *
     * @return string
     */
    public function getCertificate(): string
    {
        return $this->certificate;
    }


    /**
     * Set the value of the certificate-property
     *
     * @param string $certificate
     * @return void
     */
    private function setCertificate(string $certificate): void
    {
        $this->certificate = $certificate;
    }


    /**
     * Convert XML into a X509Certificate
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'X509Certificate');
        Assert::same($xml->namespaceURI, X509Certificate::NS);

        return new self($xml->textContent);
    }


    /**
     * Convert this X509Certificate element to XML.
     *
     * @param \DOMElement|null $parent The element we should append this X509Certificate element to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->certificate;

        return $e;
    }
}
