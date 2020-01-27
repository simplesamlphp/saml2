<?php

namespace SAML2;

use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * An interface describing signed elements.
 *
 * @package SimpleSAMLphp
 */
interface SignedElementInterface
{
    /**
     * Retrieve the certificates that are included in the message.
     *
     * @return array An array of certificates
     */
    public function getCertificates(): array;


    /**
     * Set the certificates that should be included in the element.
     * The certificates should be strings with the PEM encoded data.
     *
     * @param array $certificates An array of certificates.
     * @return void
     */
    public function setCertificates(array $certificates): void;


    /**
     * Retrieve the private key we should use to sign the message.
     *
     * @return \RobRichards\XMLSecLibs\XMLSecurityKey|null The key, or NULL if no key is specified
     */
    public function getSignatureKey(): ?XMLSecurityKey;


    /**
     * Set the private key we should use to sign the message.
     *
     * If the key is null, the message will be sent unsigned.
     *
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey|null $signatureKey
     * @return void
     */
    public function setSignatureKey(XMLSecurityKey $signatureKey = null): void;


    /**
     * Validate this element against a public key.
     *
     * If no signature is present, false is returned. If a signature is present,
     * but cannot be verified, an exception will be thrown.
     *
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey $key The key we should check against.
     * @return bool True if successful, false if we don't have a signature that can be verified.
     */
    public function validate(XMLSecurityKey $key): bool;
}
