<?php

declare(strict_types=1);

namespace SAML2\Response;

use DOMDocument;
use Mockery;
use SAML2\Assertion\Processor as AssertionProcessor;
use SAML2\CertificatesMock;
use SAML2\Configuration\Destination;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\ServiceProvider;
use SAML2\Response\Exception\UnsignedResponseException;
use SAML2\Response\Processor as ResponseProcessor;
use SAML2\Utilities\ArrayCollection;
use SAML2\Utilities\Certificate;
use SAML2\XML\saml\Assertion;
use SAML2\XML\samlp\Response;

/**
 * Test that ensures that either the response or the assertion(s) or both must be signed.
 */
class SignatureValidationTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
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
        preg_match($pattern, CertificatesMock::PUBLIC_KEY_PEM, $matches);

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

        $processor = new ResponseProcessor(new \Psr\Log\NullLogger());

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
    public function testThatAnSignedResponseWithAnUnsignedAssertionCanBeProcessed(): void
    {
        $this->assertionProcessor->shouldReceive('decryptAssertions')
            ->once()
            ->andReturn(new ArrayCollection());

        $this->assertionProcessor->shouldReceive('processAssertions')->once();

        $processor = new ResponseProcessor(new \Psr\Log\NullLogger());

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

        $processor = new ResponseProcessor(new \Psr\Log\NullLogger());

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

        $processor = new ResponseProcessor(new \Psr\Log\NullLogger());

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
        $response = Response::fromXML($doc->firstChild);
        $response->setSigningKey(CertificatesMock::getPrivateKey());
        $response->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);

        // convert to signed response
        return Response::fromXML($response->toSignedXML());
    }


    /**
     * @return \SAML2\XML\samlp\Response
     */
    private function getUnsignedResponseWithSignedAssertion(): Response
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/response.xml');
        $response = Response::fromXML($doc->firstChild);

        $assertions = $response->getAssertions();
        $assertion = $assertions[0];
        $assertion->setSigningKey(CertificatesMock::getPrivateKey());
        $assertion->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);
        $signedAssertion = new Assertion($assertion->toXML());

        $response->setAssertions([$signedAssertion]);

        return $response;
    }


    /**
     * @return \SAML2\XML\samlp\Response
     */
    private function getSignedResponseWithSignedAssertion(): Response
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/response.xml');
        $response = Response::fromXML($doc->firstChild);
        $response->setSigningKey(CertificatesMock::getPrivateKey());
        $response->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);

        $assertions = $response->getAssertions();
        $assertion  = $assertions[0];
        $assertion->setSigningKey(CertificatesMock::getPrivateKey());
        $assertion->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);

        return Response::fromXML($response->toSignedXML());
    }


    /**
     * @return \SAML2\XML\samlp\Response
     */
    private function getUnsignedResponseWithUnsignedAssertion(): Response
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '/response.xml');
        return Response::fromXML($doc->firstChild);
    }
}
