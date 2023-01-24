<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Certificate;

use SimpleSAML\SAML2\Certificate\Exception\InvalidCertificateStructureException;
use SimpleSAML\SAML2\Certificate\Exception\NoKeysFoundException;
use SimpleSAML\SAML2\Certificate\KeyCollection;
use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\SAML2\Exception\InvalidArgumentException;
use SimpleSAML\SAML2\Utilities\File;
use SimpleSAML\XMLSecurity\Utils\Certificate;

use function count;
use function preg_match;
use function sprintf;

/**
 * KeyLoader
 */
class KeyLoader
{
    /**
     * @var \SimpleSAML\SAML2\Certificate\KeyCollection
     */
    private KeyCollection $loadedKeys;


    /**
     * Constructor for KeyLoader.
     */
    public function __construct()
    {
        $this->loadedKeys = new KeyCollection();
    }


    /**
     * Extracts the public keys given by the configuration. Mainly exists for BC purposes.
     * Prioritisation order is keys > certData > certificate
     *
     * @param \SimpleSAML\SAML2\Configuration\CertificateProvider $config
     * @param string|null $usage
     * @param bool $required
     * @return \SimpleSAML\SAML2\Certificate\KeyCollection
     */
    public static function extractPublicKeys(
        CertificateProvider $config,
        string $usage = null,
        bool $required = false,
    ): KeyCollection {
        $keyLoader = new self();

        return $keyLoader->loadKeysFromConfiguration($config, $usage, $required);
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\CertificateProvider $config
     * @param null|string $usage
     * @param bool $required
     * @return \SimpleSAML\SAML2\Certificate\KeyCollection
     */
    public function loadKeysFromConfiguration(
        CertificateProvider $config,
        string $usage = null,
        bool $required = false,
    ): KeyCollection {
        $keys = $config->getKeys();
        $certificateData = $config->getCertificateData();
        $certificateFile = $config->getCertificateFile();

        if ($keys !== null) {
            $this->loadKeys($keys, $usage);
        } elseif ($certificateData !== null) {
            $this->loadCertificateData($certificateData);
        } elseif ($certificateFile !== null) {
            $this->loadCertificateFile($certificateFile);
        }

        if ($required && !$this->hasKeys()) {
            throw new NoKeysFoundException(
                'No keys found in configured metadata, please ensure that either the "keys", "certData" or '
                . '"certificate" entries is available.'
            );
        }

        return $this->getKeys();
    }


    /**
     * Loads the keys given, optionally excluding keys when a usage is given and they
     * are not configured to be used with the usage given
     *
     * @param array|\Traversable $configuredKeys
     * @param string|null $usage
     */
    public function loadKeys($configuredKeys, string $usage = null): void
    {
        foreach ($configuredKeys as $keyData) {
            if (isset($keyData['X509Certificate'])) {
                $key = new X509($keyData);
            } else {
                $key = new Key($keyData);
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
    public function loadCertificateData(string $certificateData): void
    {
        $this->loadedKeys->add(X509::createFromCertificateData($certificateData));
    }


    /**
     * Loads the certificate in the file given
     *
     * @param string $certificateFile the full path to the cert file.
     */
    public function loadCertificateFile(string $certificateFile): void
    {
        $certificate = File::getFileContents($certificateFile);
        if (!Certificate::hasValidStructure($certificate)) {
            throw new InvalidCertificateStructureException(sprintf(
                'Could not find PEM encoded certificate in "%s"',
                $certificateFile
            ));
        }

        // capture the certificate contents without the delimiters
        preg_match(Certificate::PUBLIC_KEY_PATTERN, $certificate, $matches);
        $this->loadedKeys->add(X509::createFromCertificateData($matches[1]));
    }


    /**
     * @return \SimpleSAML\SAML2\Certificate\KeyCollection
     */
    public function getKeys(): KeyCollection
    {
        return $this->loadedKeys;
    }


    /**
     * @return bool
     */
    public function hasKeys(): bool
    {
        return !!count($this->loadedKeys);
    }
}
