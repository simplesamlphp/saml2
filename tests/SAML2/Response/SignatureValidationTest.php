<?php

declare(strict_types=1);

namespace SAML2\Response;

use DOMDocument;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\Assertion\Processor as AssertionProcessor;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Response\Exception\UnsignedResponseException;
use SimpleSAML\SAML2\Response\Processor as ResponseProcessor;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\SAML2\Utilities\Certificate;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\TestUtils\PEMCertificatesMock;

/**
 * Test that ensures that either the response or the assertion(s) or both must be signed.
 *
 * @covers \SAML2\Response\SignatureValidation
 * @package simplesamlphp/saml2
 */
final class SignatureValidationTest extends MockeryTestCase
{
    /**
     * @var \SAML2\Configuration\IdentityProvider
     */
    private $identityProviderConfiguration;

    /**
     * @var \SAML2\Configuration\ServiceProvider
     */
    private $serviceProviderConfiguration;

    /**
     * @var \Mockery\MockInterface Mock of \SAML2\Assertion\ProcessorBuilder
     */
    private $assertionProcessorBuilder;

    /**
     * @var \Mockery\MockInterface Mock of \SAML2\Assertion\Processor
     */
    private $assertionProcessor;

    /**
     * @var string
     */
    private $currentDestination = 'http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php';


    /**
     * We mock the actual assertion processing as that is not what we want to test here. Since the assertion processor
     * is created via a static ::build() method we have to mock that, and have to run the tests in separate processes
     * @return void
     */
    public function setUp(): void
    {
        $this->assertionProcessorBuilder = Mockery::mock('alias:SAML2\Assertion\ProcessorBuilder');
        $this->assertionProcessor = Mockery::mock(AssertionProcessor::class);
        $this->assertionProcessorBuilder
            ->shouldReceive('build')
            ->once()
            ->andReturn($this->assertionProcessor);

        $pattern = Certificate::CERTIFICATE_PATTERN;
        preg_match($pattern, PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY), $matches);

        $this->identityProviderConfiguration
            = new IdentityProvider(['certificateData' => $matches[1]]);
        $this->serviceProviderConfiguration
            = new ServiceProvider(['entityId' => 'urn:mace:feide.no:services:no.feide.moodle']);
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testThatAnUnsignedResponseWithASignedAssertionCanBeProcessed(): void
    {
        $this->assertionProcessor->shouldReceive('decryptAssertions')
            ->once()
            ->andReturn(new ArrayCollection());

        $this->assertionProcessor->shouldReceive('processAssertions')->once();

        $processor = new ResponseProcessor(new NullLogger());

        $processor->process(
            $this->serviceProviderConfiguration,
            $this->identityProviderConfiguration,
            new Destination($this->currentDestination),
            $this->getUnsignedResponseWithSignedAssertion()
        );
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testThatASignedResponseWithAnUnsignedAssertionCanBeProcessed(): void
    {
        $this->assertionProcessor->shouldReceive('decryptAssertions')
            ->once()
            ->andReturn(new ArrayCollection());

        $this->assertionProcessor->shouldReceive('processAssertions')->once();

        $processor = new ResponseProcessor(new NullLogger());

        $processor->process(
            $this->serviceProviderConfiguration,
            $this->identityProviderConfiguration,
            new Destination($this->currentDestination),
            $this->getSignedResponseWithUnsignedAssertion()
        );
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testThatASignedResponseWithASignedAssertionIsValid(): void
    {
        $this->assertionProcessor->shouldReceive('decryptAssertions')
            ->once()
            ->andReturn(new ArrayCollection());

        $this->assertionProcessor->shouldReceive('processAssertions')->once();

        $processor = new ResponseProcessor(new NullLogger());

        $processor->process(
            $this->serviceProviderConfiguration,
            $this->identityProviderConfiguration,
            new Destination($this->currentDestination),
            $this->getSignedResponseWithSignedAssertion()
        );
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testThatAnUnsignedResponseWithNoSignedAssertionsThrowsAnException(): void
    {
        $this->expectException(UnsignedResponseException::class);

        $assertion = Mockery::mock(Assertion::class);

        // The processAssertions is called to decrypt possible encrypted assertions,
        // after which it should fail with an exception due to having no signature
        $this->assertionProcessor->shouldReceive('decryptAssertions')
            ->once()
            ->andReturn(new ArrayCollection([
                $assertion
            ]));

        $processor = new ResponseProcessor(new NullLogger());

        $processor->process(
            $this->serviceProviderConfiguration,
            $this->identityProviderConfiguration,
            new Destination($this->currentDestination),
            $this->getUnsignedResponseWithUnsignedAssertion()
        );
    }


    /**
     * @return \SAML2\XML\samlp\Response
     */
    private function getSignedResponseWithUnsignedAssertion(): Response
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/response.xml');
        $response = Response::fromXML($doc->documentElement);
        $response->setSigningKey(
            PEMCertificatesMock::getPrivateKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::PRIVATE_KEY)
        );
        $response->setCertificates([PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY)]);

        // convert to signed response
        return Response::fromXML($response->toXML());
    }


    /**
     * @return \SAML2\XML\samlp\Response
     */
    private function getUnsignedResponseWithSignedAssertion(): Response
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/unsignedResponseWithSignedAssertion.xml');
        return Response::fromXML($doc->documentElement);
    }


    /**
     * @return \SAML2\XML\samlp\Response
     */
    private function getSignedResponseWithSignedAssertion(): Response
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/unsignedResponseWithSignedAssertion.xml');
        $response = Response::fromXML($doc->documentElement);
        $response->setSigningKey(
            PEMCertificatesMock::getPrivateKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::PRIVATE_KEY)
        );
        $response->setCertificates([PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY)]);

        return Response::fromXML($response->toXML());
    }


    /**
     * @return \SAML2\XML\samlp\Response
     */
    private function getUnsignedResponseWithUnsignedAssertion(): Response
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/response.xml');
        return Response::fromXML($doc->documentElement);
    }
}
