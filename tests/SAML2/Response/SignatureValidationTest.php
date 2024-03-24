<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
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
 * @package simplesamlphp/saml2
 */
#[CoversClass(ResponseProcessor::class)]
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
     * We mock the actual assertion processing as that is not what we want to test here. Since the assertion processor
     * is created via a static ::build() method we have to mock that, and have to run the tests in separate processes
     */
    protected function setUp(): void
    {
        $this->assertionProcessorBuilder = Mockery::mock('alias:SimpleSAML\SAML2\Assertion\ProcessorBuilder');
        $this->assertionProcessor = Mockery::mock(AssertionProcessor::class);
        $this->assertionProcessorBuilder
            ->shouldReceive('build')
            ->once()
            ->andReturn($this->assertionProcessor);

        $pattern = Certificate::CERTIFICATE_PATTERN;
        preg_match($pattern, PEMCertificatesMock::loadPlainCertificateFile(PEMCertificatesMock::CERTIFICATE), $matches);

        $this->identityProviderConfiguration
            = new IdentityProvider(['certificateData' => $matches[1]]);
        $this->serviceProviderConfiguration
            = new ServiceProvider(['entityId' => 'urn:mace:feide.no:services:no.feide.moodle']);
    }


    /**
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
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
            new Destination(C::ENTITY_OTHER),
            $this->getUnsignedResponseWithSignedAssertion(),
        );
    }


    /**
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
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
            new Destination(C::ENTITY_OTHER),
            $this->getSignedResponseWithUnsignedAssertion(),
        );
    }


    /**
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
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
            new Destination(C::ENTITY_OTHER),
            $this->getSignedResponseWithSignedAssertion(),
        );
    }


    /**
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
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
            new Destination(C::ENTITY_OTHER),
            $this->getUnsignedResponseWithUnsignedAssertion(),
        );
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\Response
     */
    private function getSignedResponseWithUnsignedAssertion(): Response
    {
        $doc = DOMDocumentFactory::fromFile(
            dirname(__DIR__, 2) . '/resources/xml/response/signedresponse_with_unsignedassertion.xml',
        );

        return Response::fromXML($doc->documentElement);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\Response
     */
    private function getUnsignedResponseWithSignedAssertion(): Response
    {
        $doc = DOMDocumentFactory::fromFile(
            dirname(__DIR__, 2) . '/resources/xml/response/unsignedresponse_with_signedassertion.xml',
        );

        return Response::fromXML($doc->documentElement);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\Response
     */
    private function getSignedResponseWithSignedAssertion(): Response
    {
        $doc = DOMDocumentFactory::fromFile(
            dirname(__DIR__, 2) . '/resources/xml/response/signedresponse_with_signedassertion.xml',
        );

        return Response::fromXML($doc->documentElement);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\Response
     */
    private function getUnsignedResponseWithUnsignedAssertion(): Response
    {
        $doc = DOMDocumentFactory::fromFile(
            dirname(__DIR__, 2) . '/resources/xml/samlp_Response.xml',
        );

        return Response::fromXML($doc->documentElement);
    }
}
