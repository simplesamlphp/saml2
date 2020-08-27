<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\TestUtils\PEMCertificatesMock;

/**
 * Class \SAML2\XML\ds\X509CertificateTest
 *
 * @covers \SAML2\XML\ds\X509Certificate
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class X509CertificateTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;

    /** @var string */
    private $certificate;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $ns = X509Certificate::NS;

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
            PEMCertificatesMock::getPlainPublicKey(
                PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY,
                XMLSecurityKey::RSA_SHA256
            )
        );

        $this->document = DOMDocumentFactory::fromString(<<<XML
<ds:X509Certificate xmlns:ds="{$ns}">{$this->certificate}</ds:X509Certificate>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $X509cert = new X509Certificate($this->certificate);

        $this->assertEquals($this->certificate, $X509cert->getCertificate());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($X509cert));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $X509cert = X509Certificate::fromXML($this->document->documentElement);

        $this->assertEquals($this->certificate, $X509cert->getCertificate());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(X509Certificate::fromXML($this->document->documentElement))))
        );
    }
}
