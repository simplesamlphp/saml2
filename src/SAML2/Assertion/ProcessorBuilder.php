<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assertion;

use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Assertion\Transformer\DecodeBase64Transformer;
use SimpleSAML\SAML2\Assertion\Transformer\NameIdDecryptionTransformer;
use SimpleSAML\SAML2\Assertion\Transformer\TransformerChain;
use SimpleSAML\SAML2\Assertion\Validation\AssertionValidator;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotBefore;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SpIsValidAudience;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationMethod;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotBefore;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationNotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationRecipientMatches;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationValidator;
use SimpleSAML\SAML2\Certificate\PrivateKeyLoader;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\XML\samlp\Response;

/**
 * Simple Builder that allows to build a new Assertion Processor.
 *
 * This is an excellent candidate for refactoring towards dependency injection
 */
class ProcessorBuilder
{
    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \SimpleSAML\SAML2\Signature\Validator $signatureValidator
     * @param \SimpleSAML\SAML2\Configuration\Destination $currentDestination
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     * @return \SimpleSAML\SAML2\Assertion\Processor
     */
    public static function build(
        LoggerInterface $logger,
        Validator $signatureValidator,
        Destination $currentDestination,
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider,
        Response $response
    ): Processor {
        $keyloader = new PrivateKeyLoader();
        $decrypter = new Decrypter($logger, $identityProvider, $serviceProvider, $keyloader);
        $assertionValidator = self::createAssertionValidator($identityProvider, $serviceProvider);
        $subjectConfirmationValidator = self::createSubjectConfirmationValidator(
            $identityProvider,
            $serviceProvider,
            $currentDestination,
            $response
        );

        $transformerChain = self::createAssertionTransformerChain(
            $logger,
            $keyloader,
            $identityProvider,
            $serviceProvider
        );

        return new Processor(
            $decrypter,
            $signatureValidator,
            $assertionValidator,
            $subjectConfirmationValidator,
            $transformerChain,
            $identityProvider,
            $logger
        );
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     * @return \SimpleSAML\SAML2\Assertion\Validation\AssertionValidator
     */
    private static function createAssertionValidator(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ): AssertionValidator {
        $validator = new AssertionValidator($identityProvider, $serviceProvider);
        $validator->addConstraintValidator(new NotBefore());
        $validator->addConstraintValidator(new NotOnOrAfter());
        $validator->addConstraintValidator(new SessionNotOnOrAfter());
        $validator->addConstraintValidator(new SpIsValidAudience());

        return $validator;
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     * @param \SimpleSAML\SAML2\Configuration\Destination $currentDestination
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     * @return \SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationValidator
     */
    private static function createSubjectConfirmationValidator(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider,
        Destination $currentDestination,
        Response $response
    ): SubjectConfirmationValidator {
        $validator = new SubjectConfirmationValidator($identityProvider, $serviceProvider);
        $validator->addConstraintValidator(
            new SubjectConfirmationMethod()
        );
        $validator->addConstraintValidator(
            new SubjectConfirmationNotBefore()
        );
        $validator->addConstraintValidator(
            new SubjectConfirmationNotOnOrAfter()
        );
        $validator->addConstraintValidator(
            new SubjectConfirmationRecipientMatches(
                $currentDestination
            )
        );
        $validator->addConstraintValidator(
            new SubjectConfirmationResponseToMatches(
                $response
            )
        );

        return $validator;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \SimpleSAML\SAML2\Certificate\PrivateKeyLoader $keyLoader
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProvider
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider $serviceProvider
     * @return \SimpleSAML\SAML2\Assertion\Transformer\TransformerChain
     */
    private static function createAssertionTransformerChain(
        LoggerInterface $logger,
        PrivateKeyLoader $keyloader,
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ): TransformerChain {
        $chain = new TransformerChain($identityProvider, $serviceProvider);
        $chain->addTransformerStep(new DecodeBase64Transformer());
        $chain->addTransformerStep(
            new NameIdDecryptionTransformer($logger, $keyloader)
        );

        return $chain;
    }
}
