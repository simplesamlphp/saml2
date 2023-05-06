<?php

declare(strict_types=1);

namespace SAML2;

use SAML2\SignedElementHelperMock;
use SAML2\CertificatesMock;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\SignedElementHelper;
use SAML2\Utils;
use SAML2\Utils\XPath;

/**
 * Class \SAML2\SignedElementHelperTest
 */
class SignedElementHelperTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var \DOMElement
     */
    private $signedMockElement;


    /**
     * Create a mock signed element called 'root'
     * @return void
     */
    public function setUp(): void
    {
        $mock = new SignedElementHelperMock();
        $mock->setSignatureKey(CertificatesMock::getPrivateKey());
        $mock->setCertificates([CertificatesMock::PUBLIC_KEY_PEM]);
        $this->signedMockElement = $mock->toSignedXML();
    }


    /**
     * First check that we are able to validate with no modifications.
     *
     * To do this we first need to copy the element and add it to it's own document again
     * @todo explain why we need to copy the element?
     * @return void
     */
    public function testValidateWithoutModification(): void
    {
        $signedMockElementCopy = Utils::copyElement($this->signedMockElement);
        $signedMockElementCopy->ownerDocument->appendChild($signedMockElementCopy);
        $tmp = new SignedElementHelperMock($signedMockElementCopy);
        $this->assertTrue($tmp->validate(CertificatesMock::getPublicKey()));
    }


    /**
     * Test the modification of references.
     * @return void
     */
    public function testValidateWithReferenceTampering(): void
    {
        // Test modification of reference.
        $signedMockElementCopy = Utils::copyElement($this->signedMockElement);
        $signedMockElementCopy->ownerDocument->appendChild($signedMockElementCopy);
        $xpCache = XPath::getXPath($signedMockElementCopy);
        $digestValueElements = XPath::xpQuery(
            $signedMockElementCopy,
            '/root/ds:Signature/ds:SignedInfo/ds:Reference/ds:DigestValue',
            $xpCache,
        );
        $this->assertCount(1, $digestValueElements);
        $digestValueElements[0]->firstChild->data = 'invalid';
        $tmp = new SignedElementHelperMock($signedMockElementCopy);
        $this->assertFalse(
            $tmp->validate(CertificatesMock::getPublicKey()),
            'When the DigestValue has been tampered with, a signature should no longer be valid'
        );
    }


    /**
     * Test that signatures no longer validate if the value has been tampered with.
     * @return void
     */
    public function testValidateWithValueTampering(): void
    {
        // Test modification of SignatureValue.
        $signedMockElementCopy = Utils::copyElement($this->signedMockElement);
        $signedMockElementCopy->ownerDocument->appendChild($signedMockElementCopy);
        $xpCache = XPath::getXPath($signedMockElementCopy);
        $digestValueElements = XPath::xpQuery(
            $signedMockElementCopy,
            '/root/ds:Signature/ds:SignatureValue',
            $xpCache,
        );
        $this->assertCount(1, $digestValueElements);
        $digestValueElements[0]->firstChild->data = 'invalid';
        $tmp = new SignedElementHelperMock($signedMockElementCopy);

        $this->expectException(\Exception::class, 'Unable to validate Signature');
        $tmp->validate(CertificatesMock::getPublicKey());
    }


    /**
     * Test that signatures contain the corresponding public keys.
     * @return void
     */
    public function testGetValidatingCertificates(): void
    {
        $certData = XMLSecurityDSig::staticGet509XCerts(CertificatesMock::PUBLIC_KEY_PEM);
        $certData = $certData[0];

        $signedMockElementCopy = Utils::copyElement($this->signedMockElement);
        $signedMockElementCopy->ownerDocument->appendChild($signedMockElementCopy);
        $tmp = new SignedElementHelperMock($signedMockElementCopy);
        $certs = $tmp->getValidatingCertificates();
        $this->assertCount(1, $certs);
        $this->assertEquals($certData, $certs[0]);

        // Test with two certificates.
        $tmpCert = '-----BEGIN CERTIFICATE-----
MIICsDCCAhmgAwIBAgIJALU2mjA9ULI2MA0GCSqGSIb3DQEBBQUAMEUxCzAJBgNV
BAYTAkFVMRMwEQYDVQQIEwpTb21lLVN0YXRlMSEwHwYDVQQKExhJbnRlcm5ldCBX
aWRnaXRzIFB0eSBMdGQwHhcNMTAwODAzMDYzNTQ4WhcNMjAwODAyMDYzNTQ4WjBF
MQswCQYDVQQGEwJBVTETMBEGA1UECBMKU29tZS1TdGF0ZTEhMB8GA1UEChMYSW50
ZXJuZXQgV2lkZ2l0cyBQdHkgTHRkMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKB
gQDG6q53nl3Gn/9JE+ZiCgEB+EPcGbvzi0NrBDkKz9SKBNflxKQ+De/OAVQ9RQZO
tEm/j0hoSCGO7maemOm1PVNtDuMchSroPs0L4szLhh6m1uMhw9RXqq34C+Cr7Wee
ZNPQTFnQhBYqnYM03/e3SeUawiZ7rGeAMJ/8BSk0CB1GAQIDAQABo4GnMIGkMB0G
A1UdDgQWBBRnHHPiQ/pV/xDZg3EBmU3ik64ORDB1BgNVHSMEbjBsgBRnHHPiQ/pV
/xDZg3EBmU3ik64ORKFJpEcwRTELMAkGA1UEBhMCQVUxEzARBgNVBAgTClNvbWUt
U3RhdGUxITAfBgNVBAoTGEludGVybmV0IFdpZGdpdHMgUHR5IEx0ZIIJALU2mjA9
ULI2MAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADgYEAScv7ee6QajoSM4c4
+fX+eYdjHFsvtqHD0ng987viS8eGjIrRfKAMHVzzs1jSU0TxMM7WUFDf6FpjW+Do
r+X+X2Al/n6aDn7qAxXbl0RZuB+saxn+yFR6HFKggwkR1L2pimCuD0gTr6LlrNgf
edF1YfJgq35hcMMLY9RE/0C0bCI=
-----END CERTIFICATE-----';
        $mock = new SignedElementHelperMock();
        $mock->setSignatureKey(CertificatesMock::getPrivateKey());
        $mock->setCertificates([$tmpCert, CertificatesMock::PUBLIC_KEY_PEM]);
        $this->signedMockElement = $mock->toSignedXML();
        $tmp = new SignedElementHelperMock($this->signedMockElement);
        $certs = $tmp->getValidatingCertificates();
        $this->assertCount(1, $certs);
        $this->assertEquals($certData, $certs[0]);
    }


    /**
     * @return void
     */
    public function testGetSignatureKeyCertificates(): void
    {
        $seh = new SignedElementHelperMock();
        $origkey = CertificatesMock::getPrivateKey();
        $origcerts = [CertificatesMock::PUBLIC_KEY_PEM];

        $seh->setSignatureKey($origkey);
        $seh->setCertificates($origcerts);

        $key = $seh->getSignatureKey();

        $this->assertInstanceOf(\RobRichards\XMLSecLibs\XMLSecurityKey::class, $key);
        $this->assertEquals($origkey, $key);

        $certs = $seh->getCertificates();
        $this->assertEquals($origcerts, $certs);
    }
}
