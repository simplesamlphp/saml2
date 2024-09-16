<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion\Transformer;

use Exception;
use Psr\Log\LoggerInterface;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assertion\Exception\NotDecryptedException;
use SimpleSAML\SAML2\Certificate\PrivateKeyLoader;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\IdentityProviderAware;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Configuration\ServiceProviderAware;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\IdentifierInterface;
use SimpleSAML\SAML2\XML\saml\Subject;

use function get_class;
use function sprintf;

final class NameIdDecryptionTransformer implements
    TransformerInterface,
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
        private PrivateKeyLoader $privateKeyLoader,
    ) {
        $this->logger = $logger;
        $this->privateKeyLoader = $privateKeyLoader;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @throws \Exception
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     */
    public function transform(Assertion $assertion): Assertion
    {
        $subject = $assertion->getSubject();
        if ($subject === null) {
            return $assertion;
        }

        $identifier = $subject->getIdentifier();
        if (!($identifier instanceof EncryptedID)) {
            return $assertion;
        }

        $decryptionKeys  = $this->privateKeyLoader->loadDecryptionKeys($this->identityProvider, $this->serviceProvider);

        $decrypted = null;
        foreach ($decryptionKeys as $index => $key) {
            try {
                $decrypted = $identifier->decrypt($key);
                $this->logger->debug(sprintf('Decrypted assertion NameId with key "#%d"', $index));
                break;
            } catch (Exception $e) {
                $this->logger->debug(sprintf(
                    'Decrypting assertion NameId with key "#%d" failed, "%s" thrown: "%s"',
                    $index,
                    get_class($e),
                    $e->getMessage(),
                ));
            }
        }

        if ($decrypted === null) {
            throw new NotDecryptedException(
                'Could not decrypt the assertion NameId with the configured keys, see the debug log for information',
            );
        }
        Assert::implementsInterface($decrypted, IdentifierInterface::class);

        return new Assertion(
            $assertion->getIssuer(),
            $assertion->getIssueInstant(),
            $assertion->getId(),
            new Subject($decrypted, $subject->getSubjectConfirmation()),
            $assertion->getConditions(),
            $assertion->getStatements(),
        );
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     */
    public function setIdentityProvider(IdentityProvider $identityProvider): void
    {
        $this->identityProvider = $identityProvider;
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     */
    public function setServiceProvider(ServiceProvider $serviceProvider): void
    {
        $this->serviceProvider = $serviceProvider;
    }
}
