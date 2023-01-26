<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion;

use Exception;
use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Assertion\Exception\NotDecryptedException;
use SimpleSAML\SAML2\Certificate\PrivateKeyLoader;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;

use function count;
use function get_class;
use function is_null;
use function sprintf;

class Decrypter
{
    /**
     * Constructor for Decrypter.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     * @param \SimpleSAML\SAML2\Certificate\PrivateKeyLoader $privateKeyLoader
     */
    public function __construct(
        private LoggerInterface $logger,
        private IdentityProvider $identityProvider,
        private ServiceProvider $serviceProvider,
        private PrivateKeyLoader $privateKeyLoader,
    ) {
    }


    /**
     * Allows for checking whether either the SP or the IdP requires assertion encryption
     *
     * @return bool
     */
    public function isEncryptionRequired(): bool
    {
        return $this->identityProvider->isAssertionEncryptionRequired()
            || $this->serviceProvider->isAssertionEncryptionRequired();
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\EncryptedAssertion $assertion
     *
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     */
    public function decrypt(EncryptedAssertion $assertion): Assertion
    {
        $decryptionKeys = $this->privateKeyLoader->loadDecryptionKeys($this->identityProvider, $this->serviceProvider);

        $blacklistedKeys = $this->identityProvider->getBlacklistedAlgorithms();
        if (is_null($blacklistedKeys)) {
            $container = ContainerSingleton::getInstance();
            $blacklistedKeys = $container->getBlacklistedEncryptionAlgorithms();
        }

        // reflects the simplesamlphp behaviour for BC, see
        // https://github.com/simplesamlphp/simplesamlphp/blob/3d735912342767d391297cc5e13272a76730aca0/modules/saml/lib/Message.php#L369
        foreach ($decryptionKeys as $index => $key) {
            try {
                $decryptedAssertion = $assertion->decrypt($key, $blacklistedKeys);
                $this->logger->debug(sprintf('Decrypted Assertion with key "#%d"', $index));

                return $decryptedAssertion;
            } catch (Exception $e) {
                $this->logger->debug(sprintf(
                    'Could not decrypt assertion with key "#%d", "%s" thrown: "%s"',
                    $index,
                    get_class($e),
                    $e->getMessage(),
                ));
            }
        }

        throw new NotDecryptedException(sprintf(
            'Could not decrypt the assertion, tried with "%d" keys. See the debug log for more information',
            count($decryptionKeys),
        ));
    }
}
