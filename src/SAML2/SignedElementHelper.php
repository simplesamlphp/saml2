<?php

declare(strict_types=1);

namespace SAML2;

use DOMElement;
use DOMNode;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Helper class for processing signed elements.
 *
 * Can either be inherited from, or can be used by proxy.
 *
 * @package SimpleSAMLphp
 */
trait SignedElementHelper
{
    /**
     * SignedElement constructor.
     *
     * @param XMLSecurityDSig|null $signature
     */
    protected $signature;

    /**
     * Available methods for validating this message.
     *
     * @var array
     */
    private $validators = [];


    /**
     * Initialize a signed element from XML.
     *
     * @param \DOMElement $xml The XML element which may be signed.
     * @return static
     */
    protected function getSignatureFromXML(DOMElement $xml): void
    {
        // validate the signature element of the message
        try {
            $sig = Utils::validateElement($xml);

            if ($sig) {
                $this->setCertificates($sig['Certificates']);
                $this->addValidator([Utils::class, 'validateSignature'], $sig);
            }
        } catch (\Exception $e) {
            // ignore signature validation errors
        }
    }


    /**
     * Add a method for validating this element.
     *
     * This function is used for custom validation extensions
     *
     * @param callable $function The function which should be called.
     * @param mixed $data The data that should be included as the first parameter to the function.
     * @return void
     */
    public function addValidator(callable $function, $data): void
    {
        $this->validators[] = [
            'Function' => $function,
            'Data' => $data,
        ];
    }


    /**
     * Validate this element against a public key.
     *
     * true is returned on success, false is returned if we don't have any
     * signature we can validate. An exception is thrown if the signature
     * validation fails.
     *
     * @param  XMLSecurityKey $key The key we should check against.
     * @return bool True on success, false when we don't have a signature.
     * @throws \Exception
     */
    public function validate(XMLSecurityKey $key): bool
    {
        if (count($this->validators) === 0) {
            return false;
        }

        $exceptions = [];

        foreach ($this->validators as $validator) {
            $function = $validator['Function'];
            $data = $validator['Data'];

            try {
                call_user_func($function, $data, $key);
                /* We were able to validate the message with this validator. */

                return true;
            } catch (\Exception $e) {
                $exceptions[] = $e;
            }
        }

        /* No validators were able to validate the message. */
        throw $exceptions[0];
    }


    /**
     * Retrieve certificates that sign this element.
     *
     * @return array Array with certificates.
     * @throws \Exception if an error occurs while trying to extract the public key from a certificate.
     */
    public function getValidatingCertificates(): array
    {
        $ret = [];
        foreach ($this->getCertificates() as $cert) {
            /* Construct a PEM formatted certificate */
            $pemCert = "-----BEGIN CERTIFICATE-----\n" .
                chunk_split($cert, 64) .
                "-----END CERTIFICATE-----\n";

            /* Extract the public key from the certificate for validation. */
            $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
            $key->loadKey($pemCert);

            try {
                /* Check the signature. */
                if ($this->validate($key)) {
                    $ret[] = $cert;
                }
            } catch (\Exception $e) {
                /* This certificate does not sign this element. */
            }
        }

        return $ret;
    }


    /**
     * Sign the given XML element.
     *
     * @param \DOMElement $root The element we should sign.
     * @param \DOMNode|null $insertBefore The element we should insert the signature node before.
     * @return \DOMElement|null
     */
    protected function signElement(DOMElement $root, DOMNode $insertBefore = null): ?DOMElement
    {
        if ($this->signatureKey === null) {
            /* We cannot sign this element. */
            return null;
        }

        Utils::insertSignature($this->signatureKey, $this->certificates, $root, $insertBefore);

        return $root;
    }
}
