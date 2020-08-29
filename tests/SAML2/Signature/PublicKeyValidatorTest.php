<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Signature;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\NullLogger;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\Certificate\Key;
use SimpleSAML\SAML2\Certificate\KeyCollection;
use SimpleSAML\SAML2\Certificate\KeyLoader;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Signature\PublicKeyValidator;
use SimpleSAML\SAML2\SimpleTestLogger;
use SimpleSAML\SAML2\Utilities\Certificate;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\TestUtils\PEMCertificatesMock;

/**
 * @covers \SimpleSAML\SAML2\Signature\PublicKeyValidator
 * @package simplesamlphp/saml2
 */
final class PublicKeyValidatorTest extends MockeryTestCase
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
    public function itCannotValidateIfNoKeysCanBeLoaded(): void
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
    public function itWillValidateWhenKeysCanBeLoaded(): void
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
    public function nonX509KeysAreNotUsedForValidation(): void
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
    public function signedMessageWithValidSignatureIsValidatedCorrectly(): void
    {
        $pattern = Certificate::CERTIFICATE_PATTERN;
        preg_match($pattern, PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY), $matches);

        $config = new IdentityProvider(['certificateData' => $matches[1]]);
        $validator = new PublicKeyValidator(new SimpleTestLogger(), new KeyLoader());

        $doc = DOMDocumentFactory::fromFile(__DIR__ . '/response.xml');
        $response = Response::fromXML($doc->firstChild);
        $response->setSigningKey(
            PEMCertificatesMock::getPrivateKey(XMLSecurityKey::RSA_SHA256, PEMCertificatesMock::PRIVATE_KEY)
        );
        $response->setCertificates([PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY)]);

        // convert to signed response
        $response = Response::fromXML($response->toXML());

        $this->assertTrue($validator->canValidate($response, $config), 'Cannot validate the element');
        $this->assertTrue($validator->hasValidSignature($response, $config), 'The signature is not valid');
    }


    /**
     * @return \SimpleSAML\SAML2\Certificate\KeyLoader
     */
    private function prepareKeyLoader($returnValue)
    {
        return Mockery::mock(KeyLoader::class)
            ->shouldReceive('extractPublicKeys')
            ->andReturn($returnValue)
            ->getMock();
    }
}
