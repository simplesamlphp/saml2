<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Assertion\Exception\InvalidAssertionException;
use SimpleSAML\SAML2\Assertion\ProcessorBuilder;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;

/**
 * Tests for the Assertion validators
 *
 * @covers \SAML2\Assertion\Validation\AssertionValidator
 * @package simplesamlphp/saml2
 */
final class AssertionValidatorTest extends TestCase
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
        $audience = $spentity;
        $destination = 'https://example.org/authentication/sp/consume-assertion';

        $this->logger = new NullLogger();
        $this->validator = new Validator($this->logger);
        $this->destination = new Destination($destination);
        $this->response = new Response(new Status(new StatusCode()));

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
        <saml:Conditions>
          <saml:AudienceRestriction>
            <saml:Audience>$audience</saml:Audience>
          </saml:AudienceRestriction>
        </saml:Conditions>
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
        $assertion = new Assertion($this->document->firstChild);

        $result = $this->assertionProcessor->validateAssertion($assertion);
        $this->assertNull($result);
    }

    /**
     * Verifies that violations are caught
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testAssertionNonValidation(): void
    {
        $assertion = new Assertion($this->document->firstChild);
        $assertion->setValidAudiences(['https://example.edu/not-the-sp-entity-id']);

        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage(
            'The configured Service Provider [urn:mace:feide.no:services:no.feide.moodle] is not a valid audience for the assertion. Audiences: [https://example.edu/not-the-sp-entity-id]"'
        );
        $result = $this->assertionProcessor->validateAssertion($assertion);
    }
}
