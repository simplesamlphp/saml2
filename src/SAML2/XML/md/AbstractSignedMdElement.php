<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\SignedElementHelper;
use SAML2\SignedElementInterface;

/**
 * Abstract class that represents a signed metadata element.
 *
 * @package SAML2\XML\md
 */
abstract class AbstractSignedMdElement extends AbstractMdElement implements SignedElementInterface
{
    use SignedElementHelper;

    /**
     * List of certificates that should be included in the message.
     *
     * @var array
     */
    protected $certificates = [];

    /**
     * The private key we should use to sign the message.
     *
     * The private key can be null, in which case the message is sent unsigned.
     *
     * @var XMLSecurityKey|null
     */
    protected $signatureKey;


    /**
     * Retrieve the certificates that are included in the message.
     *
     * @return array An array of certificates
     */
    public function getCertificates(): array
    {
        return $this->certificates;
    }


    /**
     * Set the certificates that should be included in the element.
     * The certificates should be strings with the PEM encoded data.
     *
     * @param array $certificates An array of certificates.
     * @return void
     */
    public function setCertificates(array $certificates): void
    {
        $this->certificates = $certificates;
    }


    /**
     * Retrieve the private key we should use to sign the message.
     *
     * @return XMLSecurityKey|null The key, or NULL if no key is specified
     */
    public function getSignatureKey(): ?XMLSecurityKey
    {
        return $this->signatureKey;
    }


    /**
     * Set the private key we should use to sign the message.
     *
     * If the key is null, the message will be sent unsigned.
     *
     * @param XMLSecurityKey|null $signatureKey
     * @return void
     */
    public function setSignatureKey(XMLSecurityKey $signatureKey = null): void
    {
        $this->signatureKey = $signatureKey;
    }
}
