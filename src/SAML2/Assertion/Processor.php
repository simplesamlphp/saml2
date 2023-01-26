<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion;

use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Assertion\Exception\InvalidAssertionException;
use SimpleSAML\SAML2\Assertion\Exception\InvalidSubjectConfirmationException;
use SimpleSAML\SAML2\Assertion\Transformer\TransformerInterface;
use SimpleSAML\SAML2\Assertion\Validation\AssertionValidator;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationValidator;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Response\Exception\InvalidSignatureException;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;

use function implode;
use function sprintf;

class Processor
{
    /**
     * @param \SimpleSAML\SAML2\Assertion\Decrypter $decrypter
     * @param \SimpleSAML\SAML2\Signature\Validator $signatureValidator
     * @param \SimpleSAML\SAML2\Assertion\Validation\AssertionValidator $assertionValidator
     * @param \SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationValidator $subjectConfirmationValidator
     * @param \SimpleSAML\SAML2\Assertion\Transformer\TransformerInterface $transformer
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProviderConfiguration
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        private Decrypter $decrypter,
        private Validator $signatureValidator,
        private AssertionValidator $assertionValidator,
        private SubjectConfirmationValidator $subjectConfirmationValidator,
        private TransformerInterface $transformer,
        private IdentityProvider $identityProviderConfiguration,
        private LoggerInterface $logger,
    ) {
    }


    /**
     * Decrypt assertions, or do nothing if assertions are already decrypted.
     *
     * @param \SimpleSAML\SAML2\Utilities\ArrayCollection $assertions
     * @return \SimpleSAML\SAML2\Utilities\ArrayCollection Collection of processed assertions
     */
    public function decryptAssertions(ArrayCollection $assertions): ArrayCollection
    {
        $decrypted = new ArrayCollection();
        foreach ($assertions->getIterator() as $assertion) {
            if ($assertion instanceof EncryptedAssertion) {
                $decrypted->add($this->decryptAssertion($assertion));
            } elseif ($assertion instanceof Assertion) {
                $decrypted->add($assertion);
            } else {
                throw new InvalidAssertionException('The assertion must be of type: EncryptedAssertion or Assertion');
            }
        }

        return $decrypted;
    }

    /**
     * @param \SimpleSAML\SAML2\Utilities\ArrayCollection $assertions Collection of decrypted assertions
     * @return \SimpleSAML\SAML2\Utilities\ArrayCollection Collection of processed assertions
     */
    public function processAssertions(ArrayCollection $assertions): ArrayCollection
    {
        $processed = new ArrayCollection();
        foreach ($assertions->getIterator() as $assertion) {
            $processed->add($this->process($assertion));
        }

        return $processed;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     */
    public function process(Assertion $assertion): Assertion
    {
        if (!$assertion->wasSignedAtConstruction()) {
            $this->logger->info(sprintf(
                'Assertion with id "%s" was not signed at construction, not verifying the signature',
                $assertion->getId(),
            ));
        } else {
            $this->logger->info(sprintf('Verifying signature of Assertion with id "%s"', $assertion->getId()));

            if (!$this->signatureValidator->hasValidSignature($assertion, $this->identityProviderConfiguration)) {
                throw new InvalidSignatureException(
                    sprintf('The assertion with id "%s" does not have a valid signature', $assertion->getId()),
                );
            }
        }

        $this->validateAssertion($assertion);

        $assertion = $this->transformAssertion($assertion);

        return $assertion;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\EncryptedAssertion $assertion
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     */
    private function decryptAssertion(EncryptedAssertion $assertion): Assertion
    {
        return $this->decrypter->decrypt($assertion);
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     */
    public function validateAssertion(Assertion $assertion): void
    {
        $assertionValidationResult = $this->assertionValidator->validate($assertion);
        if (!$assertionValidationResult->isValid()) {
            throw new InvalidAssertionException(sprintf(
                'Invalid Assertion in SAML Response, errors: "%s"',
                implode('", "', $assertionValidationResult->getErrors()),
            ));
        }

        foreach ($assertion->getSubject()->getSubjectConfirmation() as $subjectConfirmation) {
            $subjectConfirmationValidationResult = $this->subjectConfirmationValidator->validate(
                $subjectConfirmation,
            );
            if (!$subjectConfirmationValidationResult->isValid()) {
                throw new InvalidSubjectConfirmationException(sprintf(
                    'Invalid SubjectConfirmation in Assertion, errors: "%s"',
                    implode('", "', $subjectConfirmationValidationResult->getErrors()),
                ));
            }
        }
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\Assertion $assertion
     * @return \SimpleSAML\SAML2\XML\saml\Assertion
     */
    private function transformAssertion(Assertion $assertion): Assertion
    {
        return $this->transformer->transform($assertion);
    }
}
