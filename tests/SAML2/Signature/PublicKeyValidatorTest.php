<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Signature;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Psr\Log\NullLogger;
use SimpleSAML\SAML2\Certificate\Key;
use SimpleSAML\SAML2\Certificate\KeyCollection;
use SimpleSAML\SAML2\Certificate\KeyLoader;
use SimpleSAML\SAML2\Configuration\CertificateProvider;
use SimpleSAML\SAML2\Configuration\IdentityProvider;
use SimpleSAML\SAML2\Response;
use SimpleSAML\SAML2\Signature\PublicKeyValidator;
use SimpleSAML\SAML2\SignedElement;
use SimpleSAML\SAML2\Utilities\Certificate;
use SimpleSAML\Test\SAML2\CertificatesMock;
use SimpleSAML\TestUtils\SimpleTestLogger;
use SimpleSAML\XML\DOMDocumentFactory;

class PublicKeyValidatorTest extends MockeryTestCase
{
    private MockInterface $mockSignedElement;
    private MockInterface $mockConfiguration;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->mockConfiguration = Mockery::mock(CertificateProvider::class);
        $this->mockSignedElement = Mockery::mock(SignedElement::class);
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
        preg_match($pattern, CertificatesMock::PUBLIC_KEY_PEM, $matches);

        $config = new IdentityProvider(['certificateData' => $matches[1]]);
        $validator = new PublicKeyValidator(new SimpleTestLogger(), new KeyLoader());

        $doc = DOMDocumentFactory::fromFile(__DIR__ . '/response.xml');
        $response = new Response($doc->firstChild);
        $response->setSignatureKey(CertificatesMock::getPrivateKey());
        $response->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);

        // convert to signed response
        $response = new Response($response->toSignedXML());

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
