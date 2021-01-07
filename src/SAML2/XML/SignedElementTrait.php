<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use DOMElement;
use DOMNode;
use Exception;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XMLSecurity\Utils as XMLSecurityUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;
use SimpleSAML\XMLSecurity\XMLSecurityKey;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Helper trait for processing signed elements.
 *
 * @package simplesamlphp/saml2
 */
trait SignedElementTrait
{
    /**
     * List of certificates that should be included in the message.
     *
     * @var string[]
     */
    protected array $certificates = [];

    /**
     * The signature of this element.
     *
     * @var \SimpleSAML\XMLSecurity\XML\ds\Signature|null $signature
     */
    protected ?Signature $signature = null;


    /**
     * The private key we should use to sign an unsigned message.
     *
     * The private key can be null, in which case we can only validate an already signed message.
     *
     * @var \SimpleSAML\XMLSecurity\XMLSecurityKey|null
     */
    protected ?XMLSecurityKey $signingKey = null;


    /**
     * Get the signature element of this object.
     *
     * @return \SimpleSAML\XMLSecurity\XML\ds\Signature|null
     */
    public function getSignature(): ?Signature
    {
        return $this->signature;
    }


    /**
     * Initialize a signed element from XML.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\Signature|null $signature The ds:Signature object
     */
    protected function setSignature(?Signature $signature): void
    {
        if ($signature) {
            $this->signature = $signature;
        }
    }


    /**
     * Retrieve the certificates that are included in the message.
     *
     * @return string[] An array of certificates
     */
    public function getCertificates(): array
    {
        return $this->certificates;
    }


    /**
     * Set the certificates that should be included in the element.
     * The certificates should be strings with the PEM encoded data.
     *
     * @param string[] $certificates An array of certificates.
     */
    public function setCertificates(array $certificates): void
    {
        Assert::allStringNotEmpty($certificates);

        $this->certificates = $certificates;
    }


    /**
     * Get the private key we should use to sign the message.
     *
     * If the key is null, the message will be sent unsigned.
     *
     * @return \SimpleSAML\XMLSecurity\XMLSecurityKey|null
     */
    public function getSigningKey(): ?XMLSecurityKey
    {
        return $this->signingKey;
    }


    /**
     * Set the private key we should use to sign the message.
     *
     * If the key is null, the message will be sent unsigned.
     *
     * @param \SimpleSAML\XMLSecurity\XMLSecurityKey|null $signingKey
     */
    public function setSigningKey(XMLSecurityKey $signingKey = null): void
    {
        $this->signingKey = $signingKey;
    }


    /**
     * Validate this element against a public key.
     *
     * true is returned on success, false is returned if we don't have any
     * signature we can validate. An exception is thrown if the signature
     * validation fails.
     *
     * @param  \SimpleSAML\XMLSecurity\XMLSecurityKey $key The key we should check against.
     * @return bool True on success, false when we don't have a signature.
     * @throws \Exception
     */
    public function validate(XMLSecurityKey $key): bool
    {
        if ($this->signature === null) {
            return false;
        }

        $signer = $this->signature->getSigner();
        Assert::eq(
            $key->getAlgorithm(),
            $this->signature->getAlgorithm(),
            'Algorithm provided in key does not match algorithm used in signature.'
        );

        // check the signature
        if ($signer->verify($key) === 1) {
            return true;
        }

        throw new Exception("Unable to validate Signature");
    }


    /**
     * Retrieve certificates that sign this element.
     *
     * @return array Array with certificates.
     * @throws \Exception if an error occurs while trying to extract the public key from a certificate.
     */
    public function getValidatingCertificates(): array
    {
        if ($this->signature === null) {
            return [];
        }
        $ret = [];
        foreach ($this->signature->getCertificates() as $cert) {
            // extract the public key from the certificate for validation.
            $key = new XMLSecurityKey($this->signature->getAlgorithm(), ['type' => 'public']);
            $key->loadKey($cert);

            try {
                // check the signature.
                if ($this->validate($key)) {
                    $ret[] = $cert;
                }
            } catch (Exception $e) {
                // this certificate does not sign this element.
            }
        }

        return $ret;
    }


    /**
     * Sign the given XML element.
     *
     * @param \DOMElement $root The element we should sign.
     * @return \DOMElement The signed element.
     * @throws \Exception If an error occurs while trying to sign.
     */
    protected function signElement(DOMElement $root, DOMNode $insertBefore = null): DOMElement
    {
        if ($this->signingKey instanceof XMLSecurityKey) {
            if ($insertBefore !== null) {
                XMLSecurityUtils::insertSignature($this->signingKey, $this->certificates, $root, $insertBefore);

                $doc = clone $root->ownerDocument;
                $this->signature = Signature::fromXML(XMLUtils::xpQuery($doc->documentElement, './ds:Signature')[0]);
            } else {
                $this->signature = new Signature($this->signingKey->getAlgorithm(), $this->certificates, $this->signingKey);
                $this->signature->toXML($root);
            }
        }
        return $root;
    }
}
