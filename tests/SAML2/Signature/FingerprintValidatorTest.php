<?php

namespace SAML2\Signature;

use SAML2\Certificate\FingerprintLoader;
use SAML2\Certificate\X509;
use SAML2\CertificatesMock;
use SAML2\Configuration\IdentityProvider;
use SAML2\DOMDocumentFactory;
use SAML2\Response;
use SAML2\SimpleTestLogger;
use SAML2\Utilities\Certificate;

class FingerprintValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $mockSignedElement;

    /**
     * @var \Mockery\MockInterface
     */
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
    public function it_cannot_validate_when_no_fingerprint_is_configured()
    {
        $this->mockConfiguration->shouldReceive('getCertificateFingerprints')->once()->andReturn(null);

        $validator = new FingerprintValidator(
            new SimpleTestLogger(),
            new FingerprintLoader()
        );

        $this->assertFalse($validator->canValidate($this->mockSignedElement, $this->mockConfiguration));
    }

    /**
     * @test
     * @group signature
     */
    public function it_cannot_validate_when_no_certificates_are_found()
    {
        $this->mockConfiguration->shouldReceive('getCertificateFingerprints')->once()->andReturn(array());
        $this->mockSignedElement->shouldReceive('getCertificates')->once()->andReturn(array());

        $validator = new FingerprintValidator(
            new SimpleTestLogger(),
            new FingerprintLoader()
        );

        $this->assertFalse($validator->canValidate($this->mockSignedElement, $this->mockConfiguration));
    }

    /**
     * @test
     * @group signature
     */
    public function signed_message_with_valid_signature_is_validated_correctly()
    {
        $pattern = Certificate::CERTIFICATE_PATTERN;
        preg_match($pattern, CertificatesMock::PUBLIC_KEY_PEM, $matches);
        $certdata = X509::createFromCertificateData($matches[1]);
        $fingerprint = $certdata->getFingerprint();
        $fingerprint_retry = $certdata->getFingerprint();
        $this->assertTrue($fingerprint->equals($fingerprint_retry), 'Cached fingerprint does not match original');

        $config    = new IdentityProvider(array('certificateFingerprints' => array($fingerprint->getRaw())));
        $validator = new FingerprintValidator(
            new SimpleTestLogger(),
            new FingerprintLoader()
        );

        $doc = DOMDocumentFactory::fromFile(__DIR__ . '/response.xml');
        $response = new Response($doc->firstChild);
        $response->setSignatureKey(CertificatesMock::getPrivateKey());
        $response->setCertificates(array(CertificatesMock::PUBLIC_KEY_PEM));

        // convert to signed response
        $response = new Response($response->toSignedXML());

        $this->assertTrue($validator->canValidate($response, $config), 'Cannot validate the element');
        $this->assertTrue($validator->hasValidSignature($response, $config), 'The signature is not valid');
    }
}
