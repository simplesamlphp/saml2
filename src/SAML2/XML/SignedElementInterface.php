<?php

namespace SAML2\XML;

use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * An interface describing signed elements.
 *
 * @package simplesamlphp/saml2
 */
interface SignedElementInterface
{
    /**
     * Retrieve the certificates that are included in the message.
     *
     * @return string[] An array of certificates
     */
    public function getCertificates(): array;


    /**
     * Set the certificates that should be included in the element.
     * The certificates should be strings with the PEM encoded data.
     *
     * @param string[] $certificates An array of certificates.
     * @return void
     */
    public function setCertificates(array $certificates): void;


    /**
     * Retrieve certificates that sign this element.
     *
     * @return array Array with certificates.
     * @throws \Exception if an error occurs while trying to extract the public key from a certificate.
     */
    public function getValidatingCertificates(): array;


    /**
     * Set the private key we should use to sign the message.
     *
     * If the key is null, the message will be sent unsigned.
     *
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey|null $signingKey
     * @return void
     */
    public function setSigningKey(XMLSecurityKey $signingKey = null): void;


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
