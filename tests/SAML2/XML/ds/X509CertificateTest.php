<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\ds\X509CertificateTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class X509CertificateTest extends \PHPUnit\Framework\TestCase
{
    /** @var string */
    private const FRAMEWORK = 'vendor/simplesamlphp/simplesamlphp-test-framework';

    /** @var string */
    private $certificate;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->certificate = str_replace(
            [
                '-----BEGIN RSA PUBLIC KEY-----',
                '-----END RSA PUBLIC KEY-----',
                "\r\n",
                "\n",
            ],
            [
                '',
                '',
                "\n",
                ''
            ],
            file_get_contents(self::FRAMEWORK . '/certificates/pem/selfsigned.example.org.crt')
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $X509cert = new X509Certificate($this->certificate);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $X509cert->toXML($document->firstChild);

        $X509CertElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'X509Certificate\' and namespace-uri()=\'' . X509Certificate::NS . '\']'
        );
        $this->assertCount(1, $X509CertElements);
        $X509CertElement = $X509CertElements[0];
        $this->assertEquals($this->certificate, $X509CertElement->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<ds:X509Certificate xmlns:ds="' . X509Certificate::NS . '">' . $this->certificate . '</ds:X509Certificate>'
        );

        $X509cert = X509Certificate::fromXML($document->firstChild);
        $this->assertEquals($this->certificate, $X509cert->getCertificate());
    }
}
