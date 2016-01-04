<?php

namespace SAML2\Response;

use SAML2\Assertion;
use SAML2\CertificatesMock;
use SAML2\Configuration\Destination;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\ServiceProvider;
use SAML2\Response;
use SAML2\Utilities\Certificate;

/**
 * Test that ensures that either the response or the assertion(s) or both must be signed.
 */
class SignatureValidationTest extends \PHPUnit_Framework_TestCase
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
     */
    public function setUp()
    {
        $this->assertionProcessorBuilder = \Mockery::mock('alias:SAML2\Assertion\ProcessorBuilder');
        $this->assertionProcessor = \Mockery::mock('SAML2\Assertion\Processor');
        $this->assertionProcessorBuilder
            ->shouldReceive('build')
            ->once()
            ->andReturn($this->assertionProcessor);

        $pattern = Certificate::CERTIFICATE_PATTERN;
        preg_match($pattern, CertificatesMock::PUBLIC_KEY_PEM, $matches);

        $this->identityProviderConfiguration
            = new IdentityProvider(array('certificateData' => $matches[1]));
        $this->serviceProviderConfiguration
            = new ServiceProvider(array('entityId' => 'urn:mace:feide.no:services:no.feide.moodle'));
    }

    /**
     * This ensures that the mockery expectations are tested. This cannot be done through the registered listener (See
     * the phpunit.xml in the /tools/phpunit directory) as the tests run in isolation.
     */
    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @runInSeparateProcess
     */
    public function testThatAnUnsignedResponseWithASignedAssertionCanBeProcessed()
    {
        $this->assertionProcessor->shouldReceive('processAssertions')->once();

        $processor = new Processor(new \Psr\Log\NullLogger());

        $processor->process(
            $this->serviceProviderConfiguration,
            $this->identityProviderConfiguration,
            new Destination($this->currentDestination),
            $this->getUnsignedResponseWithSignedAssertion()
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testThatAnSignedResponseWithAnUnsignedAssertionCanBeProcessed()
    {
        $this->assertionProcessor->shouldReceive('processAssertions')->once();

        $processor = new Processor(new \Psr\Log\NullLogger());

        $processor->process(
            $this->serviceProviderConfiguration,
            $this->identityProviderConfiguration,
            new Destination($this->currentDestination),
            $this->getSignedResponseWithUnsignedAssertion()
        );
    }

    /**
     * @runInSeparateProcess
     */
    public function testThatASignedResponseWithASignedAssertionIsValid()
    {
        $this->assertionProcessor->shouldReceive('processAssertions')->once();

        $processor = new Processor(new \Psr\Log\NullLogger());

        $processor->process(
            $this->serviceProviderConfiguration,
            $this->identityProviderConfiguration,
            new Destination($this->currentDestination),
            $this->getSignedResponseWithSignedAssertion()
        );
    }

    /**
     * @expectedException \SAML2\Response\Exception\UnsignedResponseException
     * @runInSeparateProcess
     */
    public function testThatAnUnsignedResponseWithNoSignedAssertionsThrowsAnException()
    {
        // here the processAssertions may not be called as it should fail with an exception due to having no signature
        $this->assertionProcessor->shouldReceive('processAssertions')->never();

        $processor = new Processor(new \Psr\Log\NullLogger());

        $processor->process(
            new ServiceProvider(array()),
            new IdentityProvider(array()),
            new Destination($this->currentDestination),
            $this->getUnsignedResponseWithUnsignedAssertion()
        );
    }

    /**
     * @return \SAML2\Response
     */
    private function getSignedResponseWithUnsignedAssertion()
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/response.xml');
        $response = new Response($doc->firstChild);
        $response->setSignatureKey(CertificatesMock::getPrivateKey());
        $response->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));

        // convert to signed response
        return new Response($response->toSignedXML());
    }

    /**
     * @return \SAML2\Response
     */
    private function getUnsignedResponseWithSignedAssertion()
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/response.xml');
        $response = new Response($doc->firstChild);

        $assertions = $response->getAssertions();
        $assertion = $assertions[0];
        $assertion->setSignatureKey(CertificatesMock::getPrivateKey());
        $assertion->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));
        $signedAssertion = new Assertion($assertion->toXML());

        $response->setAssertions(array($signedAssertion));

        return $response;
    }

    /**
     * @return \SAML2\Response
     */
    private function getSignedResponseWithSignedAssertion()
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/response.xml');
        $response = new Response($doc->firstChild);
        $response->setSignatureKey(CertificatesMock::getPrivateKey());
        $response->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));

        $assertions = $response->getAssertions();
        $assertion  = $assertions[0];
        $assertion->setSignatureKey(CertificatesMock::getPrivateKey());
        $assertion->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));

        return new Response($response->toSignedXML());
    }

    /**
     * @return \SAML2\Response
     */
    private function getUnsignedResponseWithUnsignedAssertion()
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/response.xml');
        return new Response($doc->firstChild);
    }
}
