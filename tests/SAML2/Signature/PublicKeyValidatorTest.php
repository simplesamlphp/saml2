<?php

namespace SAML2\Signature;

use SAML2\Certificate\Key;
use SAML2\Certificate\KeyCollection;
use SAML2\Certificate\KeyLoader;
use SAML2\CertificatesMock;
use SAML2\Configuration\IdentityProvider;
use SAML2\DOMDocumentFactory;
use SAML2\Response;
use SAML2\SimpleTestLogger;
use SAML2\Utilities\Certificate;

class PublicKeyValidatorTest extends \PHPUnit_Framework_TestCase
{
    private $mockSignedElement;
    private $mockConfiguration;

    public function setUp()
    {
        $this->mockConfiguration = \Mockery::mock('SAML2\Configuration\CertificateProvider');
        $this->mockSignedElement = \Mockery::mock('SAML2\SignedElement');
    }

    /**
     * @test
     * @group signature
     */
    public function it_cannot_validate_if_no_keys_can_be_loaded()
    {
        $keyloaderMock = $this->prepareKeyLoader(new KeyCollection());
        $validator = new PublicKeyValidator(new \Psr\Log\NullLogger(), $keyloaderMock);

        $this->assertFalse($validator->canValidate($this->mockSignedElement, $this->mockConfiguration));
    }

    /**
     * @test
     * @group signature
     */
    public function it_will_validate_when_keys_can_be_loaded()
    {
        $keyloaderMock = $this->prepareKeyLoader(new KeyCollection(array(1, 2)));
        $validator = new PublicKeyValidator(new \Psr\Log\NullLogger(), $keyloaderMock);

        $this->assertTrue($validator->canValidate($this->mockSignedElement, $this->mockConfiguration));
    }

    /**
     * @test
     * @group signature
     */
    public function non_X509_keys_are_not_used_for_validation()
    {
        $controlledCollection = new KeyCollection(array(
            new Key(array('type' => 'not_X509')),
            new Key(array('type' => 'again_not_X509'))
        ));

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
     */
    public function signed_message_with_valid_signature_is_validated_correctly()
    {
        $pattern = Certificate::CERTIFICATE_PATTERN;
        preg_match($pattern, CertificatesMock::PUBLIC_KEY_PEM, $matches);

        $config = new IdentityProvider(array('certificateData' => $matches[1]));
        $validator = new PublicKeyValidator(new SimpleTestLogger(), new KeyLoader());

        $doc = DOMDocumentFactory::fromFile(__DIR__ . '/response.xml');
        $response = new Response($doc->firstChild);
        $response->setSignatureKey(CertificatesMock::getPrivateKey());
        $response->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));

        // convert to signed response
        $response = new Response($response->toSignedXML());

        $this->assertTrue($validator->canValidate($response, $config), 'Cannot validate the element');
        $this->assertTrue($validator->hasValidSignature($response, $config), 'The signature is not valid');
    }

    private function prepareKeyLoader($returnValue)
    {
        return \Mockery::mock('SAML2\Certificate\KeyLoader')
            ->shouldReceive('extractPublicKeys')
            ->andReturn($returnValue)
            ->getMock();
    }
}
