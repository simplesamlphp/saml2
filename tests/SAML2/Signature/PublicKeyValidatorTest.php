<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Signature;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
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
use SimpleSAML\XMLSecurity\XML\SignedElementInterface;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(PublicKeyValidator::class)]
final class PublicKeyValidatorTest extends MockeryTestCase
{
    /** @var \SimpleSAML\XMLSecurity\XML\SignedElementInterface */
    private SignedElementInterface $mockSignedElement;

    /** @var \SimpleSAML\SAML2\Configuration\CertificateProvider */
    private CertificateProvider $mockConfiguration;


    /**
     */
    public function setUp(): void
    {
        $this->mockConfiguration = Mockery::mock(CertificateProvider::class);
        $this->mockSignedElement = Mockery::mock(AbstractMessage::class);
    }


    /**
     */
    #[Group('signature')]
    public function testItCannotValidateIfNoKeysCanBeLoaded(): void
    {
        $keyloaderMock = $this->prepareKeyLoader(new KeyCollection());
        $validator = new PublicKeyValidator(new NullLogger(), $keyloaderMock);

        $this->assertFalse($validator->canValidate($this->mockSignedElement, $this->mockConfiguration));
    }


    /**
     */
    #[Group('signature')]
    public function testItWillValidateWhenKeysCanBeLoaded(): void
    {
        $keyloaderMock = $this->prepareKeyLoader(new KeyCollection([1, 2]));
        $validator = new PublicKeyValidator(new NullLogger(), $keyloaderMock);

        $this->assertTrue($validator->canValidate($this->mockSignedElement, $this->mockConfiguration));
    }


    /**
     */
    #[Group('signature')]
    public function testNonX509KeysAreNotUsedForValidation(): void
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
     */
    #[Group('signature')]
    public function testSignedMessageWithValidSignatureIsValidatedCorrectly(): void
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


    private function prepareKeyLoader($returnValue)
    {
        return Mockery::mock(KeyLoader::class)
            ->shouldReceive('extractPublicKeys')
            ->andReturn($returnValue)
            ->getMock();
    }
}
