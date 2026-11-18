<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Assertion\Exception\InvalidAssertionException;
use SimpleSAML\SAML2\Assertion\Processor;
use SimpleSAML\SAML2\Assertion\ProcessorBuilder;
use SimpleSAML\SAML2\Assertion\Validation\AssertionValidator;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Type\IDValue;

/**
 * Tests for the Assertion validators
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(AssertionValidator::class)]
final class AssertionValidatorTest extends TestCase
{
    /** @var \Psr\Clock\ClockInterface */
    protected static ClockInterface $clock;

    /** @var \DOMDocument */
    protected static DOMDocument $document;

    /** @var \SimpleSAML\SAML2\Assertion\Processor */
    protected static Processor $assertionProcessor;

    /** @var \SimpleSAML\SAML2\Configuration\IdentityProvider */
    protected static IdentityProvider $identityProviderConfiguration;

    /** @var \SimpleSAML\SAML2\Configuration\ServiceProvider */
    protected static ServiceProvider $serviceProviderConfiguration;

    /** @var \Psr\Log\LoggerInterface */
    protected static LoggerInterface $logger;

    /** @var \SimpleSAML\SAML2\Response\Validation\Validator */
    protected static Validator $validator;

    /** @var \SimpleSAML\SAML2\Configuration\Destination */
    protected static Destination $destination;

    /** @var \SimpleSAML\SAML2\XML\samlp\Response */
    protected static Response $response;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        $idpentity = C::ENTITY_IDP;
        $spentity = C::ENTITY_IDP;
        $audience = $spentity;
        $destination = 'https://example.org/authentication/sp/consume-assertion';

        self::$clock = Utils::getContainer()->getClock();
        self::$logger = new NullLogger();
        self::$validator = new Validator(self::$logger);
        self::$destination = new Destination($destination);
        self::$response = new Response(
            id: IDValue::fromString('abc123'),
            status: new Status(
                new StatusCode(
                    SAMLAnyURIValue::fromString(C::STATUS_SUCCESS),
                ),
            ),
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );

        self::$identityProviderConfiguration = new IdentityProvider(['entityId' => $idpentity]);
        self::$serviceProviderConfiguration  = new ServiceProvider(['entityId' => $spentity]);

        self::$assertionProcessor = ProcessorBuilder::build(
            self::$logger,
            self::$validator,
            self::$destination,
            self::$identityProviderConfiguration,
            self::$serviceProviderConfiguration,
            self::$response,
        );

        $accr = C::AUTHNCONTEXT_CLASS_REF_LOA1;
        $nid_transient = C::NAMEID_TRANSIENT;

        self::$document = DOMDocumentFactory::fromString(
            <<<XML
    <saml:Assertion xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="_45e42090d8cbbfa52d5a394b01049fc2221e274182"
                    Version="2.0"
                    IssueInstant="2020-02-26T12:04:42Z"
                    >
        <saml:Issuer>{$idpentity}</saml:Issuer>
        <saml:Subject>
          <saml:NameID SPNameQualifier="https://sp.example.org/authentication/sp/metadata" Format="{$nid_transient}">SomeOtherNameIDValue</saml:NameID>
        </saml:Subject>
        <saml:Conditions>
          <saml:AudienceRestriction>
            <saml:Audience>{$audience}</saml:Audience>
          </saml:AudienceRestriction>
        </saml:Conditions>
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
    #[DoesNotPerformAssertions]
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testBasicValidation(): void
    {
        $assertion = Assertion::fromXML(self::$document->firstChild);
        self::$assertionProcessor->validateAssertion($assertion);
    }


    /**
     * Verifies that violations are caught
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testAssertionNonValidation(): void
    {
        $accr = C::AUTHNCONTEXT_CLASS_REF_LOA1;
        $entity_idp = C::ENTITY_IDP;
        $nid_transient = C::NAMEID_TRANSIENT;

        $document = DOMDocumentFactory::fromString(
            <<<XML
    <saml:Assertion xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="_45e42090d8cbbfa52d5a394b01049fc2221e274182"
                    Version="2.0"
                    IssueInstant="2020-02-26T12:04:42Z"
                    >
        <saml:Issuer>{$entity_idp}</saml:Issuer>
        <saml:Subject>
          <saml:NameID SPNameQualifier="https://sp.example.org/authentication/sp/metadata" Format="{$nid_transient}">SomeOtherNameIDValue</saml:NameID>
        </saml:Subject>
        <saml:Conditions>
          <saml:AudienceRestriction>
            <saml:Audience>https://example.edu/not-the-sp-entity-id</saml:Audience>
          </saml:AudienceRestriction>
        </saml:Conditions>
        <saml:AuthnStatement AuthnInstant="2010-03-05T13:34:28Z">
          <saml:AuthnContext>
            <saml:AuthnContextClassRef>{$accr}</saml:AuthnContextClassRef>
          </saml:AuthnContext>
        </saml:AuthnStatement>
    </saml:Assertion>
XML
            ,
        );

        $assertion = Assertion::fromXML($document->firstChild);

        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage(
            'The configured Service Provider [https://simplesamlphp.org/idp/metadata] is not a valid audience '
            . 'for the assertion. Audiences: [https://example.edu/not-the-sp-entity-id]"',
        );
        $result = self::$assertionProcessor->validateAssertion($assertion);
    }
}
