<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use DOMElement;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing a ds:X509Certificate element.
 *
 * @package SimpleSAMLphp
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
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $this->setCertificate($xml->textContent);
    }


    /**
     * Collect the value of the certificate-property
     *
     * @return string
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getCertificate(): string
    {
        Assert::notEmpty($this->certificate);

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
     * Convert this X509Certificate element to XML.
     *
     * @param \DOMElement $parent The element we should append this X509Certificate element to.
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->certificate);

        return Utils::addString($parent, XMLSecurityDSig::XMLDSIGNS, 'ds:X509Certificate', $this->certificate);
    }
}
