<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ds;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\TestUtils\PEMCertificatesMock;

/**
 * Class \SAML2\XML\ds\X509CertificateTest
 *
 * @covers \SimpleSAML\SAML2\XML\ds\AbstractDsElement
 * @covers \SimpleSAML\SAML2\XML\ds\X509Certificate
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
        $this->certificate = str_replace(
            [
                '-----BEGIN CERTIFICATE-----',
                '-----END CERTIFICATE-----',
                '-----BEGIN RSA PUBLIC KEY-----',
                '-----END RSA PUBLIC KEY-----',
                "\r\n",
                "\n",
            ],
            [
                '',
                '',
                '',
                '',
                "\n",
                ''
            ],
            PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY)
        );

        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/ds_X509Certificate.xml'
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
