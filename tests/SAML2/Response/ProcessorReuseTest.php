<?php

declare(strict_types=1);

namespace SAML2\Response;

use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Assertion;
use SAML2\CertificatesMock;
use SAML2\Configuration\Destination;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\ServiceProvider;
use SAML2\Response;
use SAML2\Response\Exception\UnsignedResponseException;
use SAML2\Utilities\ArrayCollection;
use SAML2\Utilities\Certificate;

/**
 * A Response\Processor instance can be reused for several messages (e.g. as a shared service in a long-running
 * worker). The "response was signed" state must therefore be per message, and must only be set once the signature
 * has actually been verified.
 */
class ProcessorReuseTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /** @var \SAML2\Configuration\IdentityProvider */
    private $identityProviderConfiguration;

    /** @var \SAML2\Configuration\ServiceProvider */
    private $serviceProviderConfiguration;

    /** @var string */
    private $currentDestination = 'http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php';


    /**
     * The assertion processing itself is mocked, as in SignatureValidationTest: what is tested here is the
     * response-level decision. ProcessorBuilder::build() is static, hence the alias mock and separate processes.
     */
    public function setUp(): void
    {
        parent::setUp();

        $assertionProcessor = \Mockery::mock(Assertion\Processor::class);
        $assertionProcessor->shouldReceive('decryptAssertions')->andReturn(new ArrayCollection());
        $assertionProcessor->shouldReceive('processAssertions')->andReturn(new ArrayCollection());
        \Mockery::mock('alias:SAML2\Assertion\ProcessorBuilder')
            ->shouldReceive('build')
            ->andReturn($assertionProcessor);

        preg_match(Certificate::CERTIFICATE_PATTERN, CertificatesMock::PUBLIC_KEY_PEM, $matches);
        $this->identityProviderConfiguration = new IdentityProvider(['certificateData' => $matches[1]]);
        $this->serviceProviderConfiguration
            = new ServiceProvider(['entityId' => 'urn:mace:feide.no:services:no.feide.moodle']);
    }


    /**
     * A genuinely signed Response must not leave the Processor treating the next, unsigned Response as signed.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testUnsignedResponseIsRejectedAfterASignedOneOnTheSameProcessor(): void
    {
        $processor = new Processor(new \Psr\Log\NullLogger());

        $this->process($processor, $this->getResponseSignedWith(CertificatesMock::getPrivateKey()));

        $this->expectException(UnsignedResponseException::class);
        $this->process($processor, $this->getUnsignedResponse());
    }


    /**
     * A Response whose signature FAILS verification must not count as signed either: otherwise a rejected message
     * would switch off the unsigned-response check for the next one.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testUnsignedResponseIsRejectedAfterARejectedSignatureOnTheSameProcessor(): void
    {
        $processor = new Processor(new \Psr\Log\NullLogger());

        try {
            $this->process($processor, $this->getResponseSignedWith($this->getUnrelatedPrivateKey()));
            $this->fail('A Response signed with a key the IdP does not have must be rejected.');
        } catch (UnsignedResponseException $e) {
            throw $e;
        } catch (\Exception $e) {
            // expected: the signature does not verify against the configured IdP certificate
        }

        $this->expectException(UnsignedResponseException::class);
        $this->process($processor, $this->getUnsignedResponse());
    }


    private function process(Processor $processor, Response $response): void
    {
        $processor->process(
            $this->serviceProviderConfiguration,
            $this->identityProviderConfiguration,
            new Destination($this->currentDestination),
            $response
        );
    }


    private function getUnsignedResponse(): Response
    {
        $doc = new \DOMDocument();
        $doc->load(__DIR__ . '/response.xml');

        return new Response($doc->firstChild);
    }


    private function getResponseSignedWith(XMLSecurityKey $key): Response
    {
        $response = $this->getUnsignedResponse();
        $response->setSignatureKey($key);
        $response->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);

        return new Response($response->toSignedXML());
    }


    private function getUnrelatedPrivateKey(): XMLSecurityKey
    {
        $pem = '';
        openssl_pkey_export(openssl_pkey_new(['private_key_bits' => 2048]), $pem);
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $key->loadKey($pem);

        return $key;
    }
}
