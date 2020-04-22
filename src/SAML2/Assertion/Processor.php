<?php

declare(strict_types=1);

namespace SAML2\Assertion;

use Psr\Log\LoggerInterface;
use SAML2\Assertion\Exception\InvalidAssertionException;
use SAML2\Assertion\Exception\InvalidSubjectConfirmationException;
use SAML2\Assertion\Transformer\TransformerInterface;
use SAML2\Assertion\Validation\AssertionValidator;
use SAML2\Assertion\Validation\SubjectConfirmationValidator;
use SAML2\Configuration\IdentityProvider;
use SAML2\Response\Exception\InvalidSignatureException;
use SAML2\Signature\Validator;
use SAML2\Utilities\ArrayCollection;
use SAML2\XML\saml\Assertion;
use SAML2\XML\saml\EncryptedAssertion;

class Processor
{
    /**
     * @var \SAML2\Assertion\Decrypter
     */
    private $decrypter;

    /**
     * @var \SAML2\Assertion\Validation\AssertionValidator
     */
    private $assertionValidator;

    /**
     * @var \SAML2\Assertion\Validation\SubjectConfirmationValidator
     */
    private $subjectConfirmationValidator;

    /**
     * @var \SAML2\Assertion\Transformer\TransformerInterface
     */
    private $transformer;

    /**
     * @var \SAML2\Signature\Validator
     */
    private $signatureValidator;

    /**
     * @var \SAML2\Configuration\IdentityProvider
     */
    private $identityProviderConfiguration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;


    /**
     * @param \SAML2\Assertion\Decrypter $decrypter
     * @param \SAML2\Signature\Validator $signatureValidator
     * @param \SAML2\Assertion\Validation\AssertionValidator $assertionValidator
     * @param \SAML2\Assertion\Validation\SubjectConfirmationValidator $subjectConfirmationValidator
     * @param \SAML2\Assertion\Transformer\TransformerInterface $transformer
     * @param \SAML2\Configuration\IdentityProvider $identityProviderConfiguration
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Decrypter $decrypter,
        Validator $signatureValidator,
        AssertionValidator $assertionValidator,
        SubjectConfirmationValidator $subjectConfirmationValidator,
        TransformerInterface $transformer,
        IdentityProvider $identityProviderConfiguration,
        LoggerInterface $logger
    ) {
        $this->assertionValidator            = $assertionValidator;
        $this->signatureValidator            = $signatureValidator;
        $this->decrypter                     = $decrypter;
        $this->subjectConfirmationValidator  = $subjectConfirmationValidator;
        $this->transformer                   = $transformer;
        $this->identityProviderConfiguration = $identityProviderConfiguration;
        $this->logger                        = $logger;
    }


    /**
     * Decrypt assertions, or do nothing if assertions are already decrypted.
     *
     * @param \SAML2\Utilities\ArrayCollection $assertions
     * @return \SAML2\Utilities\ArrayCollection Collection of processed assertions
     */
    public function decryptAssertions(ArrayCollection $assertions): ArrayCollection
    {
        $decrypted = new ArrayCollection();
        foreach ($assertions->getIterator() as $assertion) {
            $decrypted->add($this->decryptAssertion($assertion));
        }

        return $decrypted;
    }

    /**
     * @param \SAML2\Utilities\ArrayCollection $assertions Collection of decrypted assertions
     * @return \SAML2\Utilities\ArrayCollection Collection of processed assertions
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
     * @param \SAML2\XML\saml\Assertion $assertion
     * @return \SAML2\XML\saml\Assertion
     */
    public function process(Assertion $assertion): Assertion
    {
        if (!$assertion->wasSignedAtConstruction()) {
            $this->logger->info(sprintf(
                'Assertion with id "%s" was not signed at construction, not verifying the signature',
                $assertion->getId()
            ));
        } else {
            $this->logger->info(sprintf('Verifying signature of Assertion with id "%s"', $assertion->getId()));

            if (!$this->signatureValidator->hasValidSignature($assertion, $this->identityProviderConfiguration)) {
                throw new InvalidSignatureException(
                    sprintf('The assertion with id "%s" does not have a valid signature', $assertion->getId())
                );
            }
        }

        $this->validateAssertion($assertion);

        $assertion = $this->transformAssertion($assertion);

        return $assertion;
    }


    /**
     * @param \SAML2\XML\saml\EncryptedAssertion $assertion
     * @return \SAML2\XML\saml\Assertion
     */
    private function decryptAssertion(EncryptedAssertion $assertion): Assertion
    {
        return $this->decrypter->decrypt($assertion);
    }


    /**
     * @param \SAML2\XML\saml\Assertion $assertion
     * @return void
     */
    public function validateAssertion(Assertion $assertion): void
    {
        $assertionValidationResult = $this->assertionValidator->validate($assertion);
        if (!$assertionValidationResult->isValid()) {
            throw new InvalidAssertionException(sprintf(
                'Invalid Assertion in SAML Response, errors: "%s"',
                implode('", "', $assertionValidationResult->getErrors())
            ));
        }

        foreach ($assertion->getSubjectConfirmation() as $subjectConfirmation) {
            $subjectConfirmationValidationResult = $this->subjectConfirmationValidator->validate(
                $subjectConfirmation
            );
            if (!$subjectConfirmationValidationResult->isValid()) {
                throw new InvalidSubjectConfirmationException(sprintf(
                    'Invalid SubjectConfirmation in Assertion, errors: "%s"',
                    implode('", "', $subjectConfirmationValidationResult->getErrors())
                ));
            }
        }
    }


    /**
     * @param \SAML2\XML\saml\Assertion $assertion
     * @return \SAML2\XML\saml\Assertion
     */
    private function transformAssertion(Assertion $assertion): Assertion
    {
        return $this->transformer->transform($assertion);
    }
}
