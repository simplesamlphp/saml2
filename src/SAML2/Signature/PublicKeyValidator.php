<?php

class SAML2_Signature_PublicKeyValidator extends SAML2_Signature_AbstractChainedValidator
{
    /**
     * @var SAML2_Certificate_KeyCollection
     */
    private $configuredKeys;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * @param SAML2_SignedElement             $signedElement
     * @param SAML2_Configuration_Certifiable $configuration
     *
     * @return bool
     */
    public function canValidate(
        SAML2_SignedElement $signedElement,
        SAML2_Configuration_Certifiable $configuration
    ) {
        $this->configuredKeys = SAML2_Certificate_KeyLoader::extractPublicKeys($configuration);

        return !!count($this->configuredKeys);
    }

    /**
     * @param SAML2_SignedElement             $signedElement
     * @param SAML2_Configuration_Certifiable $configuration
     *
     * @return bool
     */
    public function hasValidSignature(
        SAML2_SignedElement $signedElement,
        SAML2_Configuration_Certifiable $configuration
    ) {
        $logger = $this->logger;
        $pemCandidates = $this->configuredKeys->filter(function (SAML2_Certificate_Key $key) use ($logger) {
            if (!$key instanceof SAML2_Certificate_X509) {
                $logger->debug(sprintf('Skipping unknown key type: "%s"', $key['type']));
                return FALSE;
            }

            return TRUE;
        });

        if (empty($pemCandidates)) {
            $this->logger->debug('No configured X509 certificate found to verify the signature with');

            return FALSE;
        }

        return $this->validateElementWithKeys($signedElement, $pemCandidates);
    }
}
