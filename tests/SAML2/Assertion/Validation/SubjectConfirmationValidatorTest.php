<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Assertion\Exception\InvalidSubjectConfirmationException;
use SimpleSAML\SAML2\Assertion\Processor;
use SimpleSAML\SAML2\Assertion\ProcessorBuilder;
use SimpleSAML\SAML2\Assertion\Validation\SubjectConfirmationValidator;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for the SubjectConfirmation validators
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(SubjectConfirmationValidator::class)]
final class SubjectConfirmationValidatorTest extends TestCase
{
    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;

    /** @var \DOMDocument */
    private static DOMDocument $document;

    /** @var \SimpleSAML\SAML2\Assertion\Processor */
    private static Processor $assertionProcessor;

    /** @var \SimpleSAML\SAML2\Configuration\IdentityProvider */
    private static IdentityProvider $identityProviderConfiguration;

    /** @var \SimpleSAML\SAML2\Configuration\ServiceProvider */
    private static ServiceProvider $serviceProviderConfiguration;

    /** @var \Psr\Log\LoggerInterface */
    private static LoggerInterface $logger;

    /** @var \SimpleSAML\SAML2\Response\Validation\Validator */
    private static Validator $validator;

    /** @var \SimpleSAML\SAML2\Configuration\Destination */
    private static Destination $destination;

    /** @var \SimpleSAML\SAML2\XML\samlp\Response */
    private static Response $response;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();
        self::$logger = new NullLogger();
        self::$validator = new Validator(self::$logger);
        self::$destination = new Destination(C::ENTITY_SP);
        self::$response = new Response(new Status(new StatusCode()), self::$clock->now());

        self::$identityProviderConfiguration = new IdentityProvider(['entityId' => C::ENTITY_IDP]);
        self::$serviceProviderConfiguration = new ServiceProvider(['entityId' => C::ENTITY_SP]);

        self::$assertionProcessor = ProcessorBuilder::build(
            self::$logger,
            self::$validator,
            self::$destination,
            self::$identityProviderConfiguration,
            self::$serviceProviderConfiguration,
            self::$response,
        );

        $ns_xsi = C::NS_XSI;
        $ns_xs = C::NS_XS;
        $ns_saml = C::NS_SAML;
        $nameid_persistent = C::NAMEID_PERSISTENT;
        $entity_idp = C::ENTITY_IDP;
        $entity_sp = C::ENTITY_SP;
        $entity_other = C::ENTITY_OTHER;
        $accr = C::AUTHNCONTEXT_CLASS_REF_LOA2;

        self::$document = DOMDocumentFactory::fromString(
            <<<XML
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
            ,
        );
    }

    /**
     * Verifies that the assertion validator works
     */
    public function testBasicValidation(): void
    {
        $assertion = Assertion::fromXML(self::$document->documentElement);

        $result = self::$assertionProcessor->validateAssertion($assertion);
        $this->assertNull($result);
    }

    /**
     * Verifies that SubjectConfirmation violations are caught
     */
    public function testSubjectConfirmationNonValidation(): void
    {
        $xml = self::$document->saveXML();
        $manipulated = str_replace(C::ENTITY_SP, C::ENTITY_OTHER, $xml);
        $document = DOMDocumentFactory::fromString($manipulated);
        $assertion = Assertion::fromXML($document->documentElement);

        $this->expectException(InvalidSubjectConfirmationException::class);
        $this->expectExceptionMessage(
            'Invalid SubjectConfirmation in Assertion, errors: "Recipient in SubjectConfirmationData ' .
            '("https://example.org/metadata") does not match the current destination ' .
            '("https://simplesamlphp.org/sp/metadata")',
        );
        self::$assertionProcessor->validateAssertion($assertion);
    }
}
