<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Response;

use Psr\Log\LoggerInterface;
use SimpleSAML\SAML2\Assertion\ProcessorBuilder;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Response\Exception\InvalidResponseException;
use SimpleSAML\SAML2\Response\Exception\NoAssertionsFoundException;
use SimpleSAML\SAML2\Response\Exception\PreconditionNotMetException;
use SimpleSAML\SAML2\Response\Exception\UnsignedResponseException;
use SimpleSAML\SAML2\Response\Validation\PreconditionValidator;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\SAML2\XML\samlp\Response;

use function sprintf;

class Processor
{
    /**
     * @var \SimpleSAML\SAML2\Response\Validation\PreconditionValidator
     */
    private PreconditionValidator $preconditionValidator;

    /**
     * @var \SimpleSAML\SAML2\Signature\Validator
     */
    private Validator $signatureValidator;

    /**
     * @var \SimpleSAML\SAML2\Assertion\Processor
     */
    private $assertionProcessor;

    /**
     * Indicates whether or not the response was signed. This is required in order to be able to check whether either
     * the reponse or one of its assertions was signed
     *
     * @var bool
     */
    private bool $responseIsSigned = false;


    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        private LoggerInterface $logger,
    ) {
        $this->signatureValidator = new Validator($logger);
    }


    /**
     * @param \SimpleSAML\SAML2\Configuration\ServiceProvider  $serviceProviderConfiguration
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProviderConfiguration
     * @param \SimpleSAML\SAML2\Configuration\Destination $currentDestination
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     *
     * @return \SimpleSAML\SAML2\Utilities\ArrayCollection Collection of \SimpleSAML\SAML2\XML\saml\Assertion objects
     */
    public function process(
        ServiceProvider $serviceProviderConfiguration,
        IdentityProvider $identityProviderConfiguration,
        Destination $currentDestination,
        Response $response,
    ): ArrayCollection {
        $this->preconditionValidator = new PreconditionValidator($currentDestination);
        $this->assertionProcessor = ProcessorBuilder::build(
            $this->logger,
            $this->signatureValidator,
            $currentDestination,
            $identityProviderConfiguration,
            $serviceProviderConfiguration,
            $response
        );

        $this->enforcePreconditions($response);
        $this->verifySignature($response, $identityProviderConfiguration);
        return $this->processAssertions($response);
    }


    /**
     * Checks the preconditions that must be valid in order for the response to be processed.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     * @throws \SimpleSAML\SAML2\Response\Exception\PreconditionNotMetException
     */
    private function enforcePreconditions(Response $response): void
    {
        $result = $this->preconditionValidator->validate($response);

        if (!$result->isValid()) {
            throw PreconditionNotMetException::createFromValidationResult($result);
        }
    }


    /**
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     * @param \SimpleSAML\SAML2\Configuration\IdentityProvider $identityProviderConfiguration
     * @throws \SimpleSAML\SAML2\Response\Exception\InvalidResponseException
     */
    private function verifySignature(Response $response, IdentityProvider $identityProviderConfiguration): void
    {
        if (!$response->isMessageConstructedWithSignature()) {
            $this->logger->info(sprintf(
                'SAMLResponse with id "%s" was not signed at root level, not attempting to verify the signature of the'
                . ' reponse itself',
                $response->getId()
            ));

            return;
        }

        $this->logger->info(sprintf(
            'Attempting to verify the signature of SAMLResponse with id "%s"',
            $response->getId()
        ));

        $this->responseIsSigned = true;

        if (!$this->signatureValidator->hasValidSignature($response, $identityProviderConfiguration)) {
            throw new InvalidResponseException(
                sprintf('The SAMLResponse with id "%s", does not have a valid signature', $response->getId())
            );
        }
    }


    /**
     * @param \SimpleSAML\SAML2\XML\samlp\Response $response
     * @throws \SimpleSAML\SAML2\Response\Exception\UnsignedResponseException
     * @throws \SimpleSAML\SAML2\Response\Exception\NoAssertionsFoundException
     * @return \SimpleSAML\SAML2\Utilities\ArrayCollection
     */
    private function processAssertions(Response $response): ArrayCollection
    {
        $assertions = $response->getAssertions();
        if (empty($assertions)) {
            throw new NoAssertionsFoundException('No assertions found in response from IdP.');
        }

        $decryptedAssertions = $this->assertionProcessor->decryptAssertions(
            new ArrayCollection($assertions)
        );

        if (!$this->responseIsSigned) {
            foreach ($assertions as $assertion) {
                if (!$assertion->wasSignedAtConstruction()) {
                    throw new UnsignedResponseException(
                        'Both the response and the assertion it contains are not signed.'
                    );
                }
            }
        }

        return $this->assertionProcessor->processAssertions($decryptedAssertions);
    }
}
