<?php

declare(strict_types=1);

namespace SAML2\Assertion\Transformer;

use Exception;
use Psr\Log\LoggerInterface;
use SAML2\XML\saml\Assertion;
use SAML2\Assertion\Exception\NotDecryptedException;
use SAML2\Certificate\PrivateKeyLoader;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\IdentityProviderAware;
use SAML2\Configuration\ServiceProvider;
use SAML2\Configuration\ServiceProviderAware;

final class NameIdDecryptionTransformer implements
    TransformerInterface,
    IdentityProviderAware,
    ServiceProviderAware
{
    /**
     * @var \SAML2\Certificate\PrivateKeyLoader
     */
    private $privateKeyLoader;

    /**
     * @var \SAML2\Configuration\IdentityProvider
     */
    private $identityProvider;

    /**
     * @var \SAML2\Configuration\ServiceProvider
     */
    private $serviceProvider;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;


    /**
     * Constructor for NameIdDecryptionTransformer
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \SAML2\Certificate\PrivateKeyLoader $privateKeyLoader
     */
    public function __construct(
        LoggerInterface $logger,
        PrivateKeyLoader $privateKeyLoader
    ) {
        $this->logger = $logger;
        $this->privateKeyLoader = $privateKeyLoader;
    }


    /**
     * @param \SAML2\XML\saml\Assertion $assertion
     * @throws \Exception
     * @return \SAML2\XML\saml\Assertion
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
     * @param \SAML2\Configuration\IdentityProvider $identityProvider
     * @return void
     */
    public function setIdentityProvider(IdentityProvider $identityProvider): void
    {
        $this->identityProvider = $identityProvider;
    }


    /**
     * @param \SAML2\Configuration\ServiceProvider $serviceProvider
     * @return void
     */
    public function setServiceProvider(ServiceProvider $serviceProvider): void
    {
        $this->serviceProvider = $serviceProvider;
    }
}
