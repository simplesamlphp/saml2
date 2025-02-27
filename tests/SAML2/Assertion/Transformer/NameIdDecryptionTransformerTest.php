<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Transformer;

use DOMDocument;
use PHPUnit\Framework\Attributes\{PreserveGlobalState, RunInSeparateProcess};
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\{LoggerInterface, NullLogger};
use SimpleSAML\SAML2\Assertion\{Processor, ProcessorBuilder};
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Configuration\{Destination, IdentityProvider, PrivateKey, ServiceProvider};
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\{
    Assertion,
    AuthnContext,
    AuthnContextClassRef,
    AuthnStatement,
    EncryptedID,
    Issuer,
    NameID,
    Subject,
};
use SimpleSAML\SAML2\XML\samlp\{Response, Status, StatusCode};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Type\IDValue;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function getcwd;

/**
 * Tests for decryption NameIDs.
 *
 * @package simplesamlphp/saml2
 */
final class NameIdDecryptionTransformerTest extends TestCase
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


    /** @var string */
    private const FRAMEWORK = 'vendor/simplesamlphp/xml-security';


    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();

        $container = ContainerSingleton::getInstance();
        $container->setBlacklistedAlgorithms(null);

        self::$logger = new NullLogger();
        self::$validator = new Validator(self::$logger);
        self::$destination = new Destination(C::ENTITY_SP);
        self::$response = new Response(
            id: IDValue::fromString('SomeIDValue'),
            status: new Status(
                new StatusCode(SAMLAnyURIValue::fromString(C::STATUS_SUCCESS)),
            ),
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );

        self::$identityProviderConfiguration = new IdentityProvider(['assertionEncryptionEnabled' => true]);
        $base = getcwd() . DIRECTORY_SEPARATOR . self::FRAMEWORK;
        $keysDir = 'tests' . DIRECTORY_SEPARATOR . PEMCertificatesMock::KEYS_DIR;
        self::$serviceProviderConfiguration = new ServiceProvider(
            [
                'entityId' => C::ENTITY_SP,
                'blacklistedEncryptionAlgorithms' => [],
                'privateKeys' => [
                    new PrivateKey(
                        $base . DIRECTORY_SEPARATOR . $keysDir . DIRECTORY_SEPARATOR . PEMCertificatesMock::PRIVATE_KEY,
                        'default',
                        PEMCertificatesMock::PASSPHRASE,
                        true,
                    ),
                ],
            ],
        );

        self::$assertionProcessor = ProcessorBuilder::build(
            self::$logger,
            self::$validator,
            self::$destination,
            self::$identityProviderConfiguration,
            self::$serviceProviderConfiguration,
            self::$response,
        );

        $encryptor = (new KeyTransportAlgorithmFactory([]))->getAlgorithm(
            C::KEY_TRANSPORT_RSA_1_5,
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::PUBLIC_KEY),
        );
        $nameId = new NameID(
            SAMLStringValue::fromString('value'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
        );
        $encryptedId = new EncryptedID($nameId->encrypt($encryptor));

        $assertion = new Assertion(
            issuer: new Issuer(
                SAMLStringValue::fromString(C::ENTITY_IDP),
            ),
            id: IDValue::fromString('_45e42090d8cbbfa52d5a394b01049fc2221e274182'),
            issueInstant: SAMLDateTimeValue::fromString('2023-05-27T16:20:52Z'),
            subject: new Subject($encryptedId),
            statements: [
                new AuthnStatement(
                    new AuthnContext(
                        new AuthnContextClassRef(
                            SAMLAnyURIValue::fromString(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                        ),
                        null,
                        null,
                    ),
                    SAMLDateTimeValue::fromString('2023-05-27T16:20:52Z'),
                ),
            ],
        );

        self::$document = $assertion->toXML()->ownerDocument;
    }


    /**
     * Verifies that we can create decrypted NameIDs.
     * @return void
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testBasicNameIdDecryption(): void
    {
        $this->markTestSkipped();

        $assertion = Assertion::fromXML(self::$document->documentElement);
        $processed = self::$assertionProcessor->process($assertion);
        $identifier = $processed->getSubject()->getIdentifier();

        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals('value', $identifier->getContent()->getValue());
        $this->assertEquals('urn:x-simplesamlphp:namequalifier', $identifier->getNameQualifier()->getValue());
    }


    /**
     * Run the decoder through processAssertions.
     *
     * @return void
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testDecryptionProcessAssertions(): void
    {
        $this->markTestSkipped();

        $assertion = Assertion::fromXML(self::$document->documentElement);
        $assertions = new ArrayCollection([$assertion]);

        $processed = self::$assertionProcessor->processAssertions($assertions);
        $this->assertCount(1, $processed);
        $identifier = $processed->getOnlyElement()->getSubject()->getIdentifier();

        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals('value', $identifier->getContent()->getValue());
        $this->assertEquals('urn:x-simplesamlphp:namequalifier', $identifier->getNameQualifier()->getValue());
    }
}
