<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Assertion\Exception\InvalidAssertionException;
use SimpleSAML\SAML2\Assertion\Processor;
use SimpleSAML\SAML2\Assertion\ProcessorBuilder;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\PrivateKey;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Signature\Validator;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\TestUtils\SAML2\ControlledTimeTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function getcwd;

/**
 * Tests for decryption NameIDs.
 *
 * @package simplesamlphp/saml2
 */
final class NameIdDecryptionTransformerTest extends TestCase
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

    /** @var string */
    private const FRAMEWORK = 'vendor/simplesamlphp/xml-security';


    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::parentSetUpBeforeClass();

        $container = ContainerSingleton::getInstance();
        $container->setBlacklistedAlgorithms(null);

        self::$logger = new NullLogger();
        self::$validator = new Validator(self::$logger);
        self::$destination = new Destination(C::ENTITY_SP);
        self::$response = new Response(new Status(new StatusCode()), self::$currentTime);

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
            self::$response
        );

        $encryptor = (new KeyTransportAlgorithmFactory([]))->getAlgorithm(
            C::KEY_TRANSPORT_RSA_1_5,
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::PUBLIC_KEY),
        );
        $nameId = new NameID('value', 'name_qualifier');
        $encryptedId = new EncryptedID($nameId->encrypt($encryptor));

        $assertion = new Assertion(
            issuer: new Issuer(C::ENTITY_IDP),
            id: '_45e42090d8cbbfa52d5a394b01049fc2221e274182',
            issueInstant: new DateTimeImmutable('2023-05-27T16:20:52Z'),
            subject: new Subject($encryptedId),
            statements: [
                new AuthnStatement(
                    new AuthnContext(
                        new AuthnContextClassRef(C::AUTHNCONTEXT_CLASS_REF_LOA1),
                        null,
                        null,
                    ),
                    new DateTimeImmutable('2023-05-27T16:20:52Z'),
                ),
            ],
        );

        self::$document = $assertion->toXML()->ownerDocument;
    }


    /**
     * Verifies that we can create decrypted NameIDs.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testBasicNameIdDecryption(): void
    {
        $this->markTestSkipped();

        $assertion = Assertion::fromXML(self::$document->documentElement);
        $processed = self::$assertionProcessor->process($assertion);
        $identifier = $processed->getSubject()->getIdentifier();

        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals('value', $identifier->getContent());
        $this->assertEquals('name_qualifier', $identifier->getNameQualifier());
    }


    /**
     * Run the decoder through processAssertions.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testDecryptionProcessAssertions(): void
    {
        $this->markTestSkipped();

        $assertion = Assertion::fromXML(self::$document->documentElement);
        $assertions = new ArrayCollection([$assertion]);

        $processed = self::$assertionProcessor->processAssertions($assertions);
        $this->assertCount(1, $processed);
        $identifier = $processed->getOnlyElement()->getSubject()->getIdentifier();

        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals('value', $identifier->getContent());
        $this->assertEquals('name_qualifier', $identifier->getNameQualifier());
    }
}
