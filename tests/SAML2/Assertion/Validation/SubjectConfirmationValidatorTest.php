<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SAML2\Assertion\Exception\InvalidSubjectConfirmationException;
use SAML2\Assertion\ProcessorBuilder;
use SAML2\Configuration\Destination;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\ServiceProvider;
use SAML2\DOMDocumentFactory;
use SAML2\Signature\Validator;
use SAML2\XML\samlp\Response;

/**
 * Tests for the SubjectConfirmation validators
 *
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationValidatorTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;

    /**
     * @var \SAML2\Assertion\Processor
     */
    protected $assertionProcessor;

    /**
     * @var \SAML2\Configuration\IdentityProvider
     */
    protected $identityProviderConfiguration;

    /**
     * @var \SAML2\Configuration\ServiceProvider
     */
    protected $serviceProviderConfiguration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \SAML2\Response\Validation\Validator
     */
    protected $validator;

    /**
     * @var \SAML2\Configuration\Destination
     */
    protected $destination;

    /**
     * @var \SAML2\xml\samlp\Response
     */
    protected $response;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $idpentity = 'urn:thki:sid:idp2';
        $spentity = 'urn:mace:feide.no:services:no.feide.moodle';
        $destination = 'https://example.org/authentication/sp/consume-assertion';

        $this->logger = new \Psr\Log\NullLogger();
        $this->validator = new Validator($this->logger);
        $this->destination = new Destination($destination);
        $this->response = new Response();

        $this->identityProviderConfiguration
            = new IdentityProvider(['entityId' => $idpentity]);
        $this->serviceProviderConfiguration
            = new ServiceProvider(['entityId' => $spentity]);

        $this->assertionProcessor = ProcessorBuilder::build(
            $this->logger,
            $this->validator,
            $this->destination,
            $this->identityProviderConfiguration,
            $this->serviceProviderConfiguration,
            $this->response
        );

        $this->document = DOMDocumentFactory::fromString(<<<XML
    <saml:Assertion xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="_45e42090d8cbbfa52d5a394b01049fc2221e274182"
                    Version="2.0"
                    IssueInstant="2020-02-26T12:04:42Z"
                    >
        <saml:Issuer>$idpentity</saml:Issuer>
        <saml:Conditions/>
        <saml:Subject>
            <saml:NameID SPNameQualifier="$spentity"
                         Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent"
                         >e0f2ba563f02531ece353dc389edf769ce991190</saml:NameID>
            <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
                <saml:SubjectConfirmationData NotOnOrAfter="2080-02-26T15:26:40Z"
                                              Recipient="$destination"
                                              InResponseTo="CORTO2278b437b23dfe5f13843c06dd47efc25f9e4574"
                                              />
            </saml:SubjectConfirmation>
        </saml:Subject>
    </saml:Assertion>
XML
        );
    }

    /**
     * Verifies that the assertion validator works
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testBasicValidation(): void
    {
        $assertion = new Assertion($this->document->documentElement);

        $result = $this->assertionProcessor->validateAssertion($assertion);
        $this->assertNull($result);
    }

    /**
     * Verifies that SubjectConfirmation violations are caught
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testSubjectConfirmationNonValidation(): void
    {
        $assertion = new Assertion($this->document->documentElement);

        $sc = $assertion->getSubjectConfirmation()[0];
        $scd = $sc->getSubjectConfirmationData();
        $newscd = new SubjectConfirmationData(
            $scd->getNotBefore(),
            $scd->getNotOnOrAfter(),
            "https://elsewhere.example.edu",
            $scd->getInResponseTo(),
            $scd->getAddress(),
            $scd->getInfo()
        );
        $newsc = new SubjectConfirmation($sc->getMethod(), $sc->getIdentifier(), $newscd);
        $assertion->setSubjectConfirmation([$newsc]);

        $this->expectException(InvalidSubjectConfirmationException::class);
        $this->expectExceptionMessage(
            'Invalid SubjectConfirmation in Assertion, errors: "Recipient in SubjectConfirmationData ' .
            '("https://elsewhere.example.edu") does not match the current destination ' .
            '("https://example.org/authentication/sp/consume-assertion")'
        );
        $this->assertionProcessor->validateAssertion($assertion);
    }
}
