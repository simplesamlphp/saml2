<?php

/**
 * KeyLoader
 */
class SAML2_Certificate_KeyLoader
{
    /**
     * @var SAML2_Certificate_KeyCollection
     */
    private $loadedKeys;

    public function __construct()
    {
        $this->loadedKeys = new SAML2_Certificate_KeyCollection();
    }

    /**
     * Extracts the public keys given by the configuration. Mainly exists for BC purposes.
     * Prioritisation order is keys > certDaa > certificate
     *
     * @param SAML2_Configuration_Certifiable $config
     * @param null                            $usage
     * @param bool                            $required
     * @param string                          $prefix
     *
     * @return SAML2_Certificate_KeyCollection
     */
    public static function extractPublicKeys(
        SAML2_Configuration_Certifiable $config,
        $usage = NULL,
        $required = FALSE,
        $prefix = ''
    ) {
        $keyLoader = new self();

        return $keyLoader->loadKeysFromConfiguration($config, $usage, $required, $prefix, $keyLoader);
    }

    /**
     * @param SAML2_Configuration_Certifiable $config
     * @param NULL|string                     $usage
     * @param bool                            $required
     * @param string                          $prefix
     * @return SAML2_Certificate_KeyCollection
     */
    public function loadKeysFromConfiguration(
        SAML2_Configuration_Certifiable $config,
        $usage = NULL,
        $required = FALSE,
        $prefix = ''
    ) {
        if ($config->has($prefix . 'keys')) {
            $this->loadKeys($config->get($prefix . 'keys'), $usage);
        } elseif ($config->has($prefix . 'certData')) {
            $this->loadCertificateData($config->get($prefix . 'certData'));
        } elseif ($config->has($prefix . 'certificate')) {
            $this->loadCertificateFile($config->get($prefix . 'certificate'));
        }

        if ($required && !$this->hasKeys()) {
            throw new SAML2_Certificate_Exception_NoKeysFoundException('No keys found in configured metadata');
        }

        return $this->getKeys();
    }

    /**
     * Loads the keys given, optionally excluding keys when a usage is given and they
     * are not configured to be used with the usage given
     *
     * @param array $configuredKeys
     * @param       $usage
     */
    public function loadKeys(array $configuredKeys, $usage)
    {
        foreach ($configuredKeys as $keyData) {
            if (isset($key['X509Certificate'])) {
                $key = new SAML2_Certificate_X509($keyData);
            } else {
                $key = new SAML2_Certificate_Key($keyData);
            }

            if ($usage && !$key->canBeUsedFor($usage)) {
                continue;
            }

            $this->loadedKeys->add($key);
        }
    }

    /**
     * Attempts to load a key based on the given certificateData
     *
     * @param string $certificateData
     */
    public function loadCertificateData($certificateData)
    {
        if (!is_string($certificateData)) {
            throw SAML2_Exception_InvalidArgumentException::invalidType('string', $certificateData);
        }

        $this->loadedKeys->add(SAML2_Certificate_Key::createX509key($certificateData));
    }

    /**
     * Loads the certificate in the file given
     *
     * @param string $certificateFile the full path to the cert file.
     */
    public function loadCertificateFile($certificateFile)
    {
        if (!is_readable($certificateFile)) {
            throw new SAML2_Exception_InvalidArgumentException(sprintf(
                'Certificate file "%s" does not exist or is not readable',
                $certificateFile
            ));
        }

        $certificate = file_get_contents($certificateFile);
        if ($certificate === FALSE) {
            throw new SAML2_Exception_RuntimeException(sprintf(
                'Could not read from existing and readable file "%s"',
                $certificateFile
            ));
        }

        if (!SAML2_Utilities_Certificate::hasValidStructure($certificate)) {
            throw new SAML2_Certificate_Exception_InvalidCertificateStructureException(sprintf(
                'Could not find PEM encoded certificate in "%s"',
                $certificateFile
            ));
        }

        // capture the certificate contents without the delimiters
        preg_match(SAML2_Utilities_Certificate::CERTIFICATE_PATTERN, $certificate, $matches);
        $this->loadedKeys->add(SAML2_Certificate_Key::createX509key($matches[1]));
    }

    /**
     * @return SAML2_Certificate_KeyCollection
     */
    public function getKeys()
    {
        return $this->loadedKeys;
    }

    /**
     * @return bool
     */
    public function hasKeys()
    {
        return !!count($this->loadedKeys);
    }
}
