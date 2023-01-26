<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Assertion\Exception\InvalidSubjectConfirmationException;
use SimpleSAML\SAML2\Assertion\Processor;
use SimpleSAML\SAML2\Assertion\ProcessorBuilder;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for the SubjectConfirmation validators
 *
 * @covers \SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationValidator
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationValidatorTest extends TestCase
{
    /** @var \DOMDocument */
    protected DOMDocument $document;

    /** @var \SimpleSAML\SAML2\Assertion\Processor */
    protected Processor $assertionProcessor;

    /** @var \SimpleSAML\SAML2\Configuration\IdentityProvider */
    protected IdentityProvider $identityProviderConfiguration;

    /** @var \SimpleSAML\SAML2\Configuration\ServiceProvider */
    protected ServiceProvider $serviceProviderConfiguration;

    /** @var \Psr\Log\LoggerInterface */
    protected LoggerInterface $logger;

    /** @var \SimpleSAML\SAML2\Response\Validation\Validator */
    protected Validator $validator;

    /** @var \SimpleSAML\SAML2\Configuration\Destination */
    protected Destination $destination;

    /** @var \SimpleSAML\SAML2\xml\samlp\Response */
    protected Response $response;


    /**
     */
    protected function setUp(): void
    {
        $this->logger = new NullLogger();
        $this->validator = new Validator($this->logger);
        $this->destination = new Destination(C::ENTITY_SP);
        $this->response = new Response(new Status(new StatusCode()));

        $this->identityProviderConfiguration = new IdentityProvider(['entityId' => C::ENTITY_IDP]);
        $this->serviceProviderConfiguration = new ServiceProvider(['entityId' => C::ENTITY_SP]);

        $this->assertionProcessor = ProcessorBuilder::build(
            $this->logger,
            $this->validator,
            $this->destination,
            $this->identityProviderConfiguration,
            $this->serviceProviderConfiguration,
            $this->response,
        );

        $ns_xsi = C::NS_XSI;
        $ns_xs = C::NS_XS;
        $ns_saml = C::NS_SAML;
        $nameid_persistent = C::NAMEID_PERSISTENT;
        $entity_idp = C::ENTITY_IDP;
        $entity_sp = C::ENTITY_SP;
        $entity_other = C::ENTITY_OTHER;
        $accr = C::AUTHNCONTEXT_CLASS_REF_LOA2;

        $this->document = DOMDocumentFactory::fromString(<<<XML
    <saml:Assertion xmlns:xsi="{$ns_xsi}"
                    xmlns:xs="{$ns_xs}"
                    xmlns:saml="{$ns_saml}"
                    ID="_45e42090d8cbbfa52d5a394b01049fc2221e274182"
                    Version="2.0"
                    IssueInstant="2020-02-26T12:04:42Z"
                    >
        <saml:Issuer>{$entity_idp}</saml:Issuer>
        <saml:Conditions/>
        <saml:Subject>
            <saml:NameID SPNameQualifier="{$entity_sp}"
                         Format="{$nameid_persistent}"
                         >e0f2ba563f02531ece353dc389edf769ce991190</saml:NameID>
            <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
                <saml:SubjectConfirmationData NotOnOrAfter="2080-02-26T15:26:40Z"
                                              Recipient="{$entity_sp}"
                                              InResponseTo="CORTO2278b437b23dfe5f13843c06dd47efc25f9e4574"
                                              />
            </saml:SubjectConfirmation>
        </saml:Subject>
        <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
            <saml:AuthnContext>
                <saml:AuthnContextClassRef>{$accr}</saml:AuthnContextClassRef>
            </saml:AuthnContext>
        </saml:AuthnStatement>
    </saml:Assertion>
XML
        );
    }

    /**
     * Verifies that the assertion validator works
     */
    public function testBasicValidation(): void
    {
        $assertion = Assertion::fromXML($this->document->documentElement);

        $result = $this->assertionProcessor->validateAssertion($assertion);
        $this->assertNull($result);
    }

    /**
     * Verifies that SubjectConfirmation violations are caught
     */
    public function testSubjectConfirmationNonValidation(): void
    {
        $xml = $this->document->saveXML();
        $manipulated = str_replace(C::ENTITY_SP, C::ENTITY_OTHER, $xml);
        $document = DOMDocumentFactory::fromString($manipulated);
        $assertion = Assertion::fromXML($document->documentElement);

        $this->expectException(InvalidSubjectConfirmationException::class);
        $this->expectExceptionMessage(
            'Invalid SubjectConfirmation in Assertion, errors: "Recipient in SubjectConfirmationData ' .
            '("https://example.org/metadata") does not match the current destination ' .
            '("https://simplesamlphp.org/sp/metadata")',
        );
        $this->assertionProcessor->validateAssertion($assertion);
    }
}
