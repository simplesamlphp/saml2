<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Entity;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\{
    Binding,
    Metadata,
    MetadataProviderInterface,
    StateProviderInterface,
    StorageProviderInterface,
    Utils,
};
use SimpleSAML\SAML2\Binding\HTTPArtifact;
use SimpleSAML\SAML2\Exception\{MetadataNotFoundException, RemoteException, RuntimeException};
use SimpleSAML\SAML2\Exception\Protocol\{RequestDeniedException, ResourceNotRecognizedException};
use SimpleSAML\SAML2\Process\Validator\ResponseValidator;
use SimpleSAML\SAML2\XML\saml\{
    Assertion,
    AttributeStatement,
    EncryptedAssertion,
    EncryptedAttribute,
    EncryptedID,
    Subject,
};
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException;
use SimpleSAML\XMLSecurity\XML\{
    EncryptableElementInterface,
    EncryptedElementInterface,
    SignableElementInterface,
    SignedElementInterface,
};

use function sprintf;

/**
 * Class representing a SAML 2 Service Provider.
 *
 * @package simplesamlphp/saml2
 */
final class ServiceProvider
{
    protected ?StateProviderInterface $stateProvider = null;
    protected ?StorageProviderInterface $storageProvider = null;
    protected ?Metadata\IdentityProvider $idpMetadata = null;
    protected SignatureAlgorithmFactory $signatureAlgorithmFactory;
    protected EncryptionAlgorithmFactory $encryptionAlgorithmFactory;
    protected KeyTransportAlgorithmFactory $keyTransportAlgorithmFactory;
    protected bool $responseWasSigned;

    /**
     * @param bool $encryptedAssertions  Whether assertions must be encrypted
     * @param bool $disableScoping  Whether to send the samlp:Scoping element in requests
     * @param bool $enableUnsolicited  Whether to process unsolicited responses
     * @param bool $encryptNameId  Whether to encrypt the NameID sent
     * @param bool $signAuthnRequest  Whether to sign the AuthnRequest sent
     * @param bool $signLogout  Whether to sign the LogoutRequest/LogoutResponse sent
     * @param bool $validateLogout  Whether to validate the signature of LogoutRequest/LogoutResponse received
     */
    public function __construct(
        protected MetadataProviderInterface $metadataProvider,
        protected Metadata\ServiceProvider $spMetadata,
        protected readonly bool $encryptedAssertions = false,
        protected readonly bool $disableScoping = false,
        protected readonly bool $enableUnsolicited = false,
        protected readonly bool $encryptNameId = false,
        protected readonly bool $signAuthnRequest = false,
        protected readonly bool $signLogout = false,
        protected readonly bool $validateLogout = true,
        // Use with caution - will leave any form of signature verification or token decryption up to the implementer
        protected readonly bool $bypassResponseVerification = false,
        // Use with caution - will leave any form of constraint validation up to the implementer
        protected readonly bool $bypassConstraintValidation = false,
    ) {
        $this->signatureAlgorithmFactory = new SignatureAlgorithmFactory();
        $this->encryptionAlgorithmFactory = new EncryptionAlgorithmFactory();
        $this->keyTransportAlgorithmFactory = new KeyTransportAlgorithmFactory();
    }


    /**
     */
    public function setStateProvider(StateProviderInterface $stateProvider): void
    {
        $this->stateProvider = $stateProvider;
    }


    /**
     */
    public function setStorageProvider(StorageProviderInterface $storageProvider): void
    {
        $this->storageProvider = $storageProvider;
    }


    /**
     * Receive a verified, and optionally validated Response.
     *
     * Upon receiving the response from the binding, the signature will be validated first.
     * Once the signature checks out, the assertions are decrypted, their signatures verified
     *  and then any encrypted NameID's and/or attributes are decrypted.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \SimpleSAML\SAML2\XML\samlp\Response The validated response.
     *
     * @throws \SimpleSAML\SAML2\Exception\Protocol\UnsupportedBindingException
     */
    public function receiveResponse(ServerRequestInterface $request): Response
    {
        $binding = Binding::getCurrentBinding($request);

        if ($binding instanceof HTTPArtifact) {
            if ($this->storageProvider === null) {
                throw new RuntimeException(
                    "A StorageProvider is required to use the HTTP-Artifact binding.",
                );
            }

            $artifact = $binding->receiveArtifact($request);
            $this->idpMetadata = $this->metadataProvider->getIdPMetadataForSha1($artifact->getSourceId());

            if ($this->idpMetadata === null) {
                throw new MetadataNotFoundException(sprintf(
                    'No metadata found for remote entity with SHA1 ID: %s',
                    $artifact->getSourceId(),
                ));
            }

            $binding->setIdpMetadata($this->idpMetadata);
            $binding->setSPMetadata($this->spMetadata);
        }

        $rawResponse = $binding->receive($request);
        Assert::isInstanceOf($rawResponse, Response::class, ResourceNotRecognizedException::class); // Wrong type of msg

        // Will return a raw Response prior to any form of verification
        if ($this->bypassResponseVerification === true) {
            return $rawResponse;
        }

        // Fetch the metadata for the remote entity
        if (!($binding instanceof HTTPArtifact)) {
            $this->idpMetadata = $this->metadataProvider->getIdPMetadata($rawResponse->getIssuer()->getContent());

            if ($this->idpMetadata === null) {
                throw new MetadataNotFoundException(sprintf(
                    'No metadata found for remote entity with entityID: %s',
                    $rawResponse->getIssuer()->getContent(),
                ));
            }
        }

        // Verify the signature (if any)
        $this->responseWasSigned = $rawResponse->isSigned();
        $verifiedResponse = $this->responseWasSigned ? $this->verifyElementSignature($rawResponse) : $rawResponse;

        $state = null;
        $stateId = $verifiedResponse->getInResponseTo();

        if (!empty($stateId)) {
            if ($this->stateProvider === null) {
                throw new RuntimeException(
                    "A StateProvider is required to correlate responses to their initial request.",
                );
            }

            // this should be a response to a request we sent earlier
            try {
                $state = $this->stateProvider::loadState($stateId, 'saml:sp:sso');
            } catch (RuntimeException $e) {
                // something went wrong,
                Utils::getContainer()->getLogger()->warning(sprintf(
                    'Could not load state specified by InResponseTo: %s; processing response as unsolicited.',
                    $e->getMessage(),
                ));
            }
        }

        $issuer = $verifiedResponse->getIssuer()->getContent();
        if ($state === null) {
            if ($this->enableUnsolicited === false) {
                throw new RequestDeniedException('Unsolicited responses are denied by configuration.');
            }
        } else {
            // check that the issuer is the one we are expecting
            Assert::keyExists($state, 'ExpectedIssuer');

            if ($state['ExpectedIssuer'] !== $issuer) {
                throw new ResourceNotRecognizedException("Issuer doesn't match the one the AuthnRequest was sent to.");
            }
        }

        $this->idpMetadata = $this->metadataProvider->getIdPMetadata($issuer);
        if ($this->idpMetadata === null) {
            throw new MetadataNotFoundException(sprintf(
                'No metadata found for remote identity provider with entityID: %s',
                $issuer,
            ));
        }

        $responseValidator = ResponseValidator::createResponseValidator(
            $this->idpMetadata,
            $this->spMetadata,
            $binding,
        );
        $responseValidator->validate($verifiedResponse);

        if ($this->encryptedAssertions === true) {
            Assert::allIsInstanceOf($verifiedResponse->getAssertions(), EncryptedAssertion::class);
        }

        // Decrypt and verify assertions, then rebuild the response.
        $verifiedAssertions = $this->decryptAndVerifyAssertions($verifiedResponse->getAssertions());
        $decryptedResponse = new Response(
            $verifiedResponse->getStatus(),
            $verifiedResponse->getIssueInstant(),
            $verifiedResponse->getIssuer(),
            $verifiedResponse->getID(),
            $verifiedResponse->getVersion(),
            $verifiedResponse->getInResponseTo(),
            $verifiedResponse->getDestination(),
            $verifiedResponse->getConsent(),
            $verifiedResponse->getExtensions(),
            $verifiedAssertions,
        );


        // Will return a verified and fully decrypted Response prior to any form of validation
        if ($this->bypassConstraintValidation === true) {
            return $decryptedResponse;
        }

        // TODO: Validate assertions
        return $decryptedResponse;
    }


    /**
     * Process the assertions and decrypt any encrypted elements inside.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Assertion[] $unverifiedAssertions
     * @return \SimpleSAML\SAML2\XML\saml\Assertion[]
     *
     * @throws \SimpleSAML\SAML2\Exception\RuntimeException if none of the keys could be used to decrypt the element
     */
    protected function decryptAndVerifyAssertions(array $unverifiedAssertions): array
    {
        $wantAssertionsSigned = $this->spMetadata->getWantAssertionsSigned();

        /**
         * See paragraph 6.2 of the SAML 2.0 core specifications for the applicable processing rules
         *
         * Long story short - Decrypt the assertion first, then validate it's signature
         * Once the signature is verified, decrypt any BaseID, NameID or Attribute that's encrypted
         */
        $verifiedAssertions = [];
        foreach ($unverifiedAssertions as $i => $assertion) {
            // Decrypt the assertions
            $decryptedAssertion = ($assertion instanceof EncryptedAssertion)
                ? $this->decryptElement($assertion)
                : $assertion;

            // Verify that the request is signed, if we require this by configuration
            if ($wantAssertionsSigned === true) {
                Assert::true($decryptedAssertion->isSigned(), RuntimeException::class);
            }

            // Verify the signature on the assertions (if any)
            $verifiedAssertion = $this->verifyElementSignature($decryptedAssertion);

            // Decrypt the NameID and replace it inside the assertion's Subject
            $nameID = $verifiedAssertion->getSubject()?->getIdentifier();

            if ($nameID instanceof EncryptedID) {
                $decryptedNameID = $this->decryptElement($nameID);
                // Anything we can't decrypt, we leave up for the application to deal with
                try {
                    $subject = new Subject($decryptedNameID, $verifiedAssertion->getSubjectConfirmation());
                } catch (RuntimeException) {
                    $subject = $verifiedAssertion->getSubject();
                }
            } else {
                $subject = $verifiedAssertion->getSubject();
            }

            // Decrypt any occurrences of EncryptedAttribute and replace them inside the assertion's AttributeStatement
            $statements = $verifiedAssertion->getStatements();
            foreach ($verifiedAssertion->getStatements() as $j => $statement) {
                if ($statement instanceof AttributeStatement) {
                    $attributes = $statement->getAttributes();
                    if ($statement->hasEncryptedAttributes()) {
                        foreach ($statement->getEncryptedAttributes() as $encryptedAttribute) {
                            // Anything we can't decrypt, we leave up for the application to deal with
                            try {
                                $attributes[] = $this->decryptElement($encryptedAttribute);
                            } catch (RuntimeException) {
                                $attributes[] = $encryptedAttribute;
                            }
                        }
                    }

                    $statements[$j] = new AttributeStatement($attributes);
                }
            }

            // Rebuild the Assertion
            $verifiedAssertions[] = new Assertion(
                $verifiedAssertion->getIssuer(),
                $verifiedAssertion->getIssueInstant(),
                $verifiedAssertion->getID(),
                $subject,
                $verifiedAssertion->getConditions(),
                $statements,
            );
        }

        return $verifiedAssertions;
    }


    /**
     * Decrypt the given element using the decryption keys provided to us.
     *
     * @param \SimpleSAML\XMLSecurity\XML\EncryptedElementInterface $element
     * @return \SimpleSAML\XMLSecurity\EncryptableElementInterface
     *
     * @throws \SimpleSAML\SAML2\Exception\RuntimeException if none of the keys could be used to decrypt the element
     */
    protected function decryptElement(EncryptedElementInterface $element): EncryptableElementInterface
    {
        // TODO: When CBC-mode encryption is used, the assertion OR the Response must be signed
        $factory = $this->encryptionAlgorithmFactory;

        // If the IDP has a pre-shared key, try decrypting with that
        $preSharedKey = $this->idpMetadata->getPreSharedKey();
        if ($preSharedKey !== null) {
            $encryptionAlgorithm = $element?->getEncryptedKey()?->getEncryptionMethod()
              ?? $this->idpMetadata->getPreSharedKeyAlgorithm();

            $decryptor = $factory->getAlgorithm($encryptionAlgorithm, $preSharedKey);
            try {
                return $element->decrypt($decryptor);
            } catch (Exception $e) {
                // Continue to try decrypting with asymmetric keys.
            }
        }

        $encryptionAlgorithm = $element->getEncryptedKey()->getEncryptionMethod()->getAlgorithm();
        foreach ($this->spMetadata->getDecryptionKeys() as $decryptionKey) {
            $factory = $this->keyTransportAlgorithmFactory;
            $decryptor = $factory->getAlgorithm($encryptionAlgorithm, $decryptionKey);
            try {
                return $element->decrypt($decryptor);
            } catch (Exception $e) {
                continue;
            }
        }

        throw new RuntimeException(sprintf(
            'Unable to decrypt %s with any of the available keys.',
            $element::class,
        ));
    }


    /**
     * Verify the signature of an element using the available validation keys.
     *
     * @param \SimpleSAML\XMLSecurity\XML\SignedElementInterface $element
     * @return \SimpleSAML\XMLSecurity\XML\SignableElementInterface The validated element.
     *
     * @throws \SimpleSAML\XMLSecurity\Exception\SignatureVerificationFailedException
     */
    protected function verifyElementSignature(SignedElementInterface $element): SignableElementInterface
    {
        $signatureAlgorithm = $element->getSignature()->getSignedInfo()->getSignatureMethod()->getAlgorithm();

        foreach ($this->idpMetadata->getValidatingKeys() as $validatingKey) {
            $verifier = $this->signatureAlgorithmFactory->getAlgorithm($signatureAlgorithm, $validatingKey);

            try {
                return $element->verify($verifier);
            } catch (SignatureVerificationFailedException $e) {
                continue;
            }
        }

        throw new SignatureVerificationFailedException();
    }
}
