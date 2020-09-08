<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ds;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XMLSecurityDSig;

/**
 * Class \SAML2\XML\ds\X509DataTest
 *
 * @covers \SimpleSAML\SAML2\XML\ds\AbstractDsElement
 * @covers \SimpleSAML\SAML2\XML\ds\X509Data
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class X509DataTest extends TestCase
{
    /** @var string */
    private const FRAMEWORK = 'vendor/simplesamlphp/simplesamlphp-test-framework';

    /** @var \DOMDocument */
    private DOMDocument $document;

    /** @var string */
    private string $certificate;

    /** @var string[] */
    private array $certData;


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

        $this->certData = openssl_x509_parse(
            PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY)
        );

        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/ds_X509Data.xml'
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $X509data = new X509Data(
            [
                new Chunk(
                    DOMDocumentFactory::fromString('<ds:X509UnknownTag>somevalue</ds:X509UnknownTag>')->documentElement
                ),
                new X509Certificate($this->certificate),
                new X509SubjectName($this->certData['name']),
                new Chunk(DOMDocumentFactory::fromString('<some>Chunk</some>')->documentElement)
            ]
        );

        $data = $X509data->getData();

        $this->assertInstanceOf(Chunk::class, $data[0]);
        $this->assertInstanceOf(X509Certificate::class, $data[1]);
        $this->assertInstanceOf(X509SubjectName::class, $data[2]);
        $this->assertInstanceOf(Chunk::class, $data[3]);

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($X509data));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $X509data = X509Data::fromXML($this->document->documentElement);

        $data = $X509data->getData();
        $this->assertInstanceOf(Chunk::class, $data[0]);
        $this->assertInstanceOf(X509Certificate::class, $data[1]);
        $this->assertInstanceOf(X509SubjectName::class, $data[2]);
        $this->assertInstanceOf(Chunk::class, $data[3]);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(X509Data::fromXML($this->document->documentElement))))
        );
    }
}
