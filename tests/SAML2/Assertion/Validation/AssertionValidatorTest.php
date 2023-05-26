<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Assertion\Exception\InvalidAssertionException;
use SimpleSAML\SAML2\Assertion\Processor;
use SimpleSAML\SAML2\Assertion\ProcessorBuilder;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\TestUtils\SAML2\ControlledTimeTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for the Assertion validators
 *
 * @covers \SimpleSAML\SAML2\Assertion\Validation\AssertionValidator
 * @package simplesamlphp/saml2
 */
final class AssertionValidatorTest extends TestCase
{
    use ControlledTimeTestTrait {
        ControlledTimeTestTrait::setUpBeforeClass as parentSetUpBeforeClass;
    }

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

    /** @var \SimpleSAML\SAML2\xml\samlp\Response */
    protected static Response $response;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::parentSetUpBeforeClass();

        $idpentity = C::ENTITY_IDP;
        $spentity = C::ENTITY_IDP;
        $audience = $spentity;
        $destination = 'https://example.org/authentication/sp/consume-assertion';

        self::$logger = new NullLogger();
        self::$validator = new Validator(self::$logger);
        self::$destination = new Destination($destination);
        self::$response = new Response(new Status(new StatusCode()), self::$currentTime);

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

        self::$document = DOMDocumentFactory::fromString(<<<XML
    <saml:Assertion xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
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
        );
    }


    /**
     * Verifies that the assertion validator works
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBasicValidation(): void
    {
        $assertion = Assertion::fromXML(self::$document->firstChild);

        $result = self::$assertionProcessor->validateAssertion($assertion);
        $this->assertNull($result);
    }

    /**

     * Verifies that violations are caught
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAssertionNonValidation(): void
    {
        $accr = C::AUTHNCONTEXT_CLASS_REF_LOA1;
        $entity_idp = C::ENTITY_IDP;
        $nid_transient = C::NAMEID_TRANSIENT;

        $document = DOMDocumentFactory::fromString(<<<XML
    <saml:Assertion xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
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
