<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Signature;

use Exception;
use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\X509Certificate as X509;
use SimpleSAML\XMLSecurity\Utils\Security;
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;

use function sprintf;

abstract class AbstractChainedValidator implements ChainedValidator
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected LoggerInterface $logger;


    /**
     * Constructor for AbstractChainedValidator
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * BC compatible version of the signature check
     *
     * @param \SimpleSAML\XMLSecurity\XML\SignedElementInterface $element
     * @param \SimpleSAML\SAML2\Utilities\ArrayCollection $pemCandidates
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function validateElementWithKeys(
        SignedElementInterface $element,
        ArrayCollection $pemCandidates
    ): bool {
        $lastException = null;
        foreach ($pemCandidates as $index => $candidateKey) {
            $cert = new X509($candidateKey->getCertificate());
            $verifier = (new SignatureAlgorithmFactory([]))->getAlgorithm(
                $element->getSignature()?->getSignedInfo()->getSignatureMethod()->getAlgorithm(),
                $cert,
            );

            try {
                /*
                 * Make sure that we have a valid signature on either the response or the assertion.
                 */
                $result = $element->verify($verifier);
                if ($result) {
                    $this->logger->debug(sprintf('Validation with key "#%d" succeeded', $index));
                    return true;
                }
                $this->logger->debug(sprintf('Validation with key "#%d" failed without exception.', $index));
            } catch (Exception $e) {
                $this->logger->debug(sprintf(
                    'Validation with key "#%d" failed with exception: %s',
                    $index,
                    $e->getMessage()
                ));

                $lastException = $e;
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        } else {
            return false;
        }
    }
}
