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
use SimpleSAML\SAML2\Signature\PublicKeyValidator;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\TestUtils\SimpleTestLogger;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

/**
 * @covers \SimpleSAML\SAML2\Signature\PublicKeyValidator
 * @package simplesamlphp/saml2
 */
final class PublicKeyValidatorTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface */
    private MockInterface $mockSignedElement;

    /** @var \Mockery\MockInterface */
    private MockInterface $mockConfiguration;


    /**
     */
    public function setUp(): void
    {
        $this->mockConfiguration = Mockery::mock(CertificateProvider::class);
        $this->mockSignedElement = Mockery::mock(AbstractMessage::class);
    }


    /**
     * @test
     * @group signature
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
     */
    public function nonX509KeysAreNotUsedForValidation(): void
    {
        $controlledCollection = new KeyCollection([
            new Key(['type' => 'not_X509']),
            new Key(['type' => 'again_not_X509']),
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
     */
    public function signedMessageWithValidSignatureIsValidatedCorrectly(): void
    {
        $config = new IdentityProvider(
            ['certificateData' => PEMCertificatesMock::getPlainCertificateContents(PEMCertificatesMock::CERTIFICATE)],
        );
        $validator = new PublicKeyValidator(new SimpleTestLogger(), new KeyLoader());

        $doc = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 3) . '/resources/xml/response/signedresponse_with_unsignedassertion.xml',
        );

        // convert to signed response
        $response = Response::fromXML($doc->documentElement);

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
