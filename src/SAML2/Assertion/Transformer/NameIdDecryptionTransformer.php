<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Transformer;

use Exception;
use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Assertion;
use SimpleSAML\SAML2\Assertion\Exception\NotDecryptedException;
use SimpleSAML\SAML2\Certificate\PrivateKeyLoader;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\IdentityProviderAware;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Configuration\ServiceProviderAware;

use function get_class;
use function sprintf;

final class NameIdDecryptionTransformer implements
    Transformer,
    IdentityProviderAware,
    ServiceProviderAware
{
    /**
     * @var \SimpleSAML\SAML2\Configuration\IdentityProvider
     */
    private IdentityProvider $identityProvider;

    /**
     * @var \SimpleSAML\SAML2\Configuration\ServiceProvider
     */
    private ServiceProvider $serviceProvider;


    /**
     * Constructor for NameIdDecryptionTransformer
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \SimpleSAML\SAML2\Certificate\PrivateKeyLoader $privateKeyLoader
     */
    public function __construct(
        private LoggerInterface $logger,
        private PrivateKeyLoader $privateKeyLoader
    ) {
    }


    /**
     * @param \SimpleSAML\SAML2\Assertion $assertion
     * @throws \Exception
     * @return \SimpleSAML\SAML2\Assertion
     */
    public function transform(Assertion $assertion): Assertion
    {
        if (!$assertion->isNameIdEncrypted()) {
            return $assertion;
        }

        $decryptionKeys  = $this->privateKeyLoader->loadDecryptionKeys($this->identityProvider, $this->serviceProvider);
        $blacklistedKeys = $this->identityProvider->getBlacklistedAlgorithms();
        if (is_null($blacklistedKeys)) {
            $blacklistedKeys = $this->serviceProvider->getBlacklistedAlgorithms();
        }

        foreach ($decryptionKeys as $index => $key) {
            try {
                $assertion->decryptNameId($key, $blacklistedKeys);
                $this->logger->debug(sprintf('Decrypted assertion NameId with key "#%d"', $index));
            } catch (Exception $e) {
                $this->logger->debug(sprintf(
                    'Decrypting assertion NameId with key "#%d" failed, "%s" thrown: "%s"',
                    $index,
                    get_class($e),
                    $e->getMessage()
                ));
            }
        }

        if ($assertion->isNameIdEncrypted()) {
            throw new NotDecryptedException(
                'Could not decrypt the assertion NameId with the configured keys, see the debug log for information'
            );
        }

        return $assertion;
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     * @return void
     */
    public function setIdentityProvider(IdentityProvider $identityProvider): void
    {
        $this->identityProvider = $identityProvider;
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     * @return void
     */
    public function setServiceProvider(ServiceProvider $serviceProvider): void
    {
        $this->serviceProvider = $serviceProvider;
    }
}
