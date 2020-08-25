<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Assertion\Exception\InvalidAssertionException;
use SAML2\Assertion\ProcessorBuilder;
use SAML2\Constants;
use SAML2\Configuration\Destination;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\PrivateKey;
use SAML2\Configuration\ServiceProvider;
use SAML2\DOMDocumentFactory;
use SAML2\Signature\Validator;
use SAML2\Utilities\ArrayCollection;
use SAML2\Utils;
use SAML2\XML\samlp\Response;
use SAML2\XML\samlp\Status;
use SAML2\XML\samlp\StatusCode;
use SimpleSAML\TestUtils\PEMCertificatesMock;

/**
 * Tests for decryption NameIDs.
 *
 * @package simplesamlphp/saml2
 */
final class NameIdDecryptionTransformerTest extends TestCase
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

    /** @var string */
    private const FRAMEWORK = '/vendor/simplesamlphp/simplesamlphp-test-framework';


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $spentity = 'urn:mace:feide.no:services:no.feide.moodle';

        $this->logger = new NullLogger();
        $this->validator = new Validator($this->logger);
        $this->destination = new Destination($spentity);
        $this->response = new Response(new Status(new StatusCode()));

        $this->identityProviderConfiguration = new IdentityProvider(['assertionEncryptionEnabled' => true]);
        $this->serviceProviderConfiguration = new ServiceProvider(
            [
                'entityId' => $spentity,
                'blacklistedEncryptionAlgorithms' => [],
                'privateKeys' => [
                    new PrivateKey(
                        getcwd() . self::FRAMEWORK . '/certificates/rsa-pem/signed.simplesamlphp.org.key',
                        'default',
                        '1234',
                        true
                    )
                ]
            ]
        );

        $this->assertionProcessor = ProcessorBuilder::build(
            $this->logger,
            $this->validator,
            $this->destination,
            $this->identityProviderConfiguration,
            $this->serviceProviderConfiguration,
            $this->response
        );

        $pubkey = new XMLSecurityKey(XMLSecurityKey::RSA_1_5, ['type' => 'public']);
        $pubkey->loadKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY));

        $assertion = new Assertion(
            new Issuer('urn:thki:sid:idp2'),
            '_45e42090d8cbbfa52d5a394b01049fc2221e274182',
            1582718682,
            new Subject(EncryptedID::fromUnencryptedElement(new NameID('value', 'name_qualifier'), $pubkey)),
            null,
            [
                new AuthnStatement(
                    new AuthnContext(
                        new AuthnContextClassRef('someAuthnContext'),
                        null,
                        null
                    ),
                    1583415268
                )
            ]
        );

        $this->document = $assertion->toXML()->ownerDocument;
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
        $assertion = Assertion::fromXML($this->document->documentElement);
        $processed = $this->assertionProcessor->process($assertion);
        $identifier = $processed->getSubject()->getIdentifier();

        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals('value', $identifier->getValue());
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
        $assertion = Assertion::fromXML($this->document->documentElement);
        $assertions = new ArrayCollection([$assertion]);

        $processed = $this->assertionProcessor->processAssertions($assertions);
        $this->assertCount(1, $processed);
        $identifier = $processed->getOnlyElement()->getSubject()->getIdentifier();

        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals('value', $identifier->getValue());
        $this->assertEquals('name_qualifier', $identifier->getNameQualifier());
    }
}
