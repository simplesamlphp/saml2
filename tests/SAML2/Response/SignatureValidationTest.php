<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response;

use DOMDocument;
use Mockery;
use Mockery\MockInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Assertion\Processor as AssertionProcessor;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\ServiceProvider;
use SimpleSAML\SAML2\Response\Exception\UnsignedResponseException;
use SimpleSAML\SAML2\Response\Processor as ResponseProcessor;
use SimpleSAML\SAML2\Utilities\ArrayCollection;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\Utils\Certificate;

use function dirname;

/**
 * Test that ensures that either the response or the assertion(s) or both must be signed.
 *
 * @covers \SimpleSAML\SAML2\Response\Processor
 * @package simplesamlphp/saml2
 */
final class SignatureValidationTest extends MockeryTestCase
{
    /**
     * @var \SimpleSAML\SAML2\Configuration\IdentityProvider
     */
    private IdentityProvider $identityProviderConfiguration;

    /**
     * @var \SimpleSAML\SAML2\Configuration\ServiceProvider
     */
    private ServiceProvider $serviceProviderConfiguration;

    /**
     * @var \Mockery\MockInterface Mock of \SAML2\Assertion\ProcessorBuilder
     */
    private MockInterface $assertionProcessorBuilder;

    /**
     * @var \Mockery\MockInterface Mock of \SAML2\Assertion\Processor
     */
    private MockInterface $assertionProcessor;

    /**
     * @var string
     */
    private string $currentDestination = C::ENTITY_OTHER;


    /**
     * We mock the actual assertion processing as that is not what we want to test here. Since the assertion processor
     * is created via a static ::build() method we have to mock that, and have to run the tests in separate processes
     */
    public function setUp(): void
    {
        $this->assertionProcessorBuilder = Mockery::mock('alias:SimpleSAML\SAML2\Assertion\ProcessorBuilder');
        $this->assertionProcessor = Mockery::mock(AssertionProcessor::class);
        $this->assertionProcessorBuilder
            ->shouldReceive('build')
            ->once()
            ->andReturn($this->assertionProcessor);

        $this->identityProviderConfiguration = new IdentityProvider(
            ['certificateData' => PEMCertificatesMock::getPlainCertificateContents(PEMCertificatesMock::CERTIFICATE)],
        );
        $this->serviceProviderConfiguration = new ServiceProvider(['entityId' => C::ENTITY_URN]);
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
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
            $this->getUnsignedResponseWithSignedAssertion(),
        );
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
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
            $this->getSignedResponseWithUnsignedAssertion(),
        );
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
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
            $this->getSignedResponseWithSignedAssertion(),
        );
    }


    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testThatAnUnsignedResponseWithNoSignedAssertionsThrowsAnException(): void
    {
        $assertion = Assertion::fromXML(
            DOMDocumentFactory::fromFile(
                dirname(__FILE__, 3) . '/resources/xml/saml_Assertion.xml',
            )->documentElement
        );

        // The processAssertions is called to decrypt possible encrypted assertions,
        // after which it should fail with an exception due to having no signature
        $this->assertionProcessor->shouldReceive('decryptAssertions')
            ->once()
            ->andReturn(new ArrayCollection([
                $assertion
            ]));

        $processor = new ResponseProcessor(new NullLogger());

        $this->expectException(UnsignedResponseException::class);
        $processor->process(
            $this->serviceProviderConfiguration,
            $this->identityProviderConfiguration,
            new Destination($this->currentDestination),
            $this->getUnsignedResponseWithUnsignedAssertion(),
        );
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\Response
     */
    private function getSignedResponseWithUnsignedAssertion(): Response
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '../../../resources/xml/response/signedresponse_with_unsignedassertion.xml');

        return Response::fromXML($doc->documentElement);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\Response
     */
    private function getUnsignedResponseWithSignedAssertion(): Response
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '../../../resources/xml/response/unsignedresponse_with_signedassertion.xml');

        return Response::fromXML($doc->documentElement);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\Response
     */
    private function getSignedResponseWithSignedAssertion(): Response
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '../../../resources/xml/response/signedresponse_with_signedassertion.xml');

        return Response::fromXML($doc->documentElement);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\Response
     */
    private function getUnsignedResponseWithUnsignedAssertion(): Response
    {
        $doc = new DOMDocument();
        $doc->load(__DIR__ . '../../../resources/xml/samlp_Response.xml');

        return Response::fromXML($doc->documentElement);
    }
}
