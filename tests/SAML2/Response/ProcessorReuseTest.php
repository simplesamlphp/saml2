<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
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
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\Utils\Certificate;

use function dirname;
use function preg_match;

/**
 * A Response\Processor instance can be reused for several messages (for example as a shared service in a
 * long-running worker). The "response is signed" state must therefore be per message, and must only be set once
 * the signature has actually been verified.
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(ResponseProcessor::class)]
final class ProcessorReuseTest extends MockeryTestCase
{
    private IdentityProvider $identityProviderConfiguration;

    private ServiceProvider $serviceProviderConfiguration;


    /**
     * The assertion processing itself is mocked, as in SignatureValidationTest: what is tested here is the
     * response-level decision. ProcessorBuilder::build() is static, hence the alias mock and separate processes.
     * The decrypted assertion handed back is unsigned, so the unsigned-response check has something to reject.
     */
    protected function setUp(): void
    {
        $unsignedAssertion = Assertion::fromXML(
            DOMDocumentFactory::fromFile(
                dirname(__FILE__, 3) . '/resources/xml/saml_Assertion.xml',
            )->documentElement,
        );

        $assertionProcessor = Mockery::mock(AssertionProcessor::class);
        $assertionProcessor->shouldReceive('decryptAssertions')
            ->andReturn(new ArrayCollection([$unsignedAssertion]));
        $assertionProcessor->shouldReceive('processAssertions')
            ->andReturn(new ArrayCollection());
        Mockery::mock('alias:SimpleSAML\SAML2\Assertion\ProcessorBuilder')
            ->shouldReceive('build')
            ->andReturn($assertionProcessor);

        preg_match(
            Certificate::CERTIFICATE_PATTERN,
            PEMCertificatesMock::loadPlainCertificateFile(PEMCertificatesMock::CERTIFICATE),
            $matches,
        );
        $this->identityProviderConfiguration = new IdentityProvider(['certificateData' => $matches[1]]);
        $this->serviceProviderConfiguration
            = new ServiceProvider(['entityId' => 'urn:mace:feide.no:services:no.feide.moodle']);
    }


    /**
     * A genuinely signed Response must not leave the Processor treating the next, unsigned Response as signed.
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testUnsignedResponseIsRejectedAfterASignedOneOnTheSameProcessor(): void
    {
        $processor = new ResponseProcessor(new NullLogger());

        $this->process($processor, $this->getSignedResponse());

        $this->expectException(UnsignedResponseException::class);
        $this->process($processor, $this->getUnsignedResponse());
    }


    /**
     * A Response whose signature FAILS verification must not count as signed either: otherwise a rejected message
     * would switch off the unsigned-response check for the next one.
     */
    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testUnsignedResponseIsRejectedAfterARejectedSignatureOnTheSameProcessor(): void
    {
        $processor = new ResponseProcessor(new NullLogger());

        try {
            $this->process($processor, $this->getResponseSignedWithAnUnrelatedKey());
            $this->fail('A Response signed with a key the IdP does not have must be rejected.');
        } catch (UnsignedResponseException $e) {
            throw $e;
        } catch (Exception $e) {
            // expected: the signature does not verify against the configured IdP certificate
        }

        $this->expectException(UnsignedResponseException::class);
        $this->process($processor, $this->getUnsignedResponse());
    }


    private function process(ResponseProcessor $processor, Response $response): void
    {
        $processor->process(
            $this->serviceProviderConfiguration,
            $this->identityProviderConfiguration,
            new Destination(C::ENTITY_OTHER),
            $response,
        );
    }


    private function getSignedResponse(): Response
    {
        $doc = DOMDocumentFactory::fromFile(
            dirname(__DIR__, 2) . '/resources/xml/response/signedresponse_with_unsignedassertion.xml',
        );

        return Response::fromXML($doc->documentElement);
    }


    private function getUnsignedResponse(): Response
    {
        $doc = DOMDocumentFactory::fromFile(dirname(__DIR__, 2) . '/resources/xml/samlp_Response.xml');

        return Response::fromXML($doc->documentElement);
    }


    private function getResponseSignedWithAnUnrelatedKey(): Response
    {
        $signer = (new SignatureAlgorithmFactory())->getAlgorithm(
            C::SIG_RSA_SHA256,
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::OTHER_PRIVATE_KEY),
        );

        $response = $this->getUnsignedResponse();
        $response->sign($signer);

        return Response::fromXML($response->toXML());
    }
}
