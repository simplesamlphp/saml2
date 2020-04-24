<?php

declare(strict_types=1);

namespace SAML2\Signature;

use Mockery;
use Psr\Log\NullLogger;
use SAML2\CertificatesMock;
use SAML2\Certificate\Key;
use SAML2\Certificate\KeyCollection;
use SAML2\Certificate\KeyLoader;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\CertificateProvider;
use SAML2\DOMDocumentFactory;
use SAML2\Signature\PublicKeyValidator;
use SAML2\SimpleTestLogger;
use SAML2\Utilities\Certificate;
use SAML2\XML\samlp\AbstractMessage;
use SAML2\XML\samlp\Response;

class PublicKeyValidatorTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    private $mockSignedElement;
    private $mockConfiguration;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->mockConfiguration = Mockery::mock(CertificateProvider::class);
        $this->mockSignedElement = Mockery::mock(AbstractMessage::class);
    }


    /**
     * @test
     * @group signature
     * @return void
     */
    public function it_cannot_validate_if_no_keys_can_be_loaded(): void
    {
        $keyloaderMock = $this->prepareKeyLoader(new KeyCollection());
        $validator = new PublicKeyValidator(new NullLogger(), $keyloaderMock);

        $this->assertFalse($validator->canValidate($this->mockSignedElement, $this->mockConfiguration));
    }


    /**
     * @test
     * @group signature
     * @return void
     */
    public function it_will_validate_when_keys_can_be_loaded(): void
    {
        $keyloaderMock = $this->prepareKeyLoader(new KeyCollection([1, 2]));
        $validator = new PublicKeyValidator(new NullLogger(), $keyloaderMock);

        $this->assertTrue($validator->canValidate($this->mockSignedElement, $this->mockConfiguration));
    }


    /**
     * @test
     * @group signature
     * @return void
     */
    public function non_X509_keys_are_not_used_for_validation(): void
    {
        $controlledCollection = new KeyCollection([
            new Key(['type' => 'not_X509']),
            new Key(['type' => 'again_not_X509'])
        ]);

        $keyloaderMock = $this->prepareKeyLoader($controlledCollection);
        $logger = new SimpleTestLogger();
        $validator = new PublicKeyValidator($logger, $keyloaderMock);

        $this->assertTrue($validator->canValidate($this->mockSignedElement, $this->mockConfiguration));
        $this->assertFalse($validator->hasValidSignature($this->mockSignedElement, $this->mockConfiguration));
        $this->assertTrue($logger->hasMessage('Skipping unknown key type: "not_X509"'));
        $this->assertTrue($logger->hasMessage('Skipping unknown key type: "again_not_X509"'));
        $this->assertTrue($logger->hasMessage('No configured X509 certificate found to verify the signature with'));
    }


    /**
     * @test
     * @group signature
     * @return void
     */
    public function signed_message_with_valid_signature_is_validated_correctly(): void
    {
        $pattern = Certificate::CERTIFICATE_PATTERN;
        preg_match($pattern, CertificatesMock::PUBLIC_KEY_PEM, $matches);

        $config = new IdentityProvider(['certificateData' => $matches[1]]);
        $validator = new PublicKeyValidator(new SimpleTestLogger(), new KeyLoader());

        $doc = DOMDocumentFactory::fromFile(__DIR__ . '/response.xml');
        $response = Response::fromXML($doc->firstChild);
        $response->setSigningKey(CertificatesMock::getPrivateKey());
        $response->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);

        // convert to signed response
        $response = Response::fromXML($response->toXML());

        $this->assertTrue($validator->canValidate($response, $config), 'Cannot validate the element');
        $this->assertTrue($validator->hasValidSignature($response, $config), 'The signature is not valid');
    }


    /**
     * @return \SAML2\Certificate\KeyLoader
     */
    private function prepareKeyLoader($returnValue)
    {
        return Mockery::mock(KeyLoader::class)
            ->shouldReceive('extractPublicKeys')
            ->andReturn($returnValue)
            ->getMock();
    }
}
