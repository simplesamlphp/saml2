<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\Chunk;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Class \SAML2\XML\ds\KeyInfoTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class KeyInfoTest extends \PHPUnit\Framework\TestCase
{
    /** @var string */
    private const FRAMEWORK = 'vendor/simplesamlphp/simplesamlphp-test-framework';

    /** @var string */
    private $certificate;

    /** @var string[] */
    private $certData;

    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $ns = KeyInfo::NS;

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
            file_get_contents(self::FRAMEWORK . '/certificates/rsa-pem/selfsigned.simplesamlphp.org.crt')
        );

        $this->certData = openssl_x509_parse(
            file_get_contents(self::FRAMEWORK . '/certificates/rsa-pem/selfsigned.simplesamlphp.org.crt')
        );

        $this->document = DOMDocumentFactory::fromString(<<<XML
<ds:KeyInfo xmlns:ds="{$ns}" Id="abc123">
  <ds:KeyName>testkey</ds:KeyName>
  <ds:X509Data>
    <ds:X509Certificate>{$this->certificate}</ds:X509Certificate>
    <ds:X509SubjectName>{$this->certData['name']}</ds:X509SubjectName>
  </ds:X509Data>
  <ds:KeySomething>Some unknown tag within the ds-namespace</ds:KeySomething>
  <some>Chunk</some>
</ds:KeyInfo>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $keyInfo = new KeyInfo(
            [
                new KeyName('testkey'),
                new X509Data(
                    [
                        new X509Certificate($this->certificate),
                        new X509SubjectName($this->certData['name'])
                    ]
                ),
                new Chunk(DOMDocumentFactory::fromString(
                    '<ds:KeySomething>Some unknown tag within the ds-namespace</ds:KeySomething>'
                )->documentElement),
                new Chunk(DOMDocumentFactory::fromString('<some>Chunk</some>')->documentElement)
            ],
            'abc123'
        );

        $info = $keyInfo->getInfo();
        $this->assertCount(4, $info);
        $this->assertInstanceOf(KeyName::class, $info[0]);
        $this->assertInstanceOf(X509Data::class, $info[1]);
        $this->assertInstanceOf(Chunk::class, $info[2]);
        $this->assertInstanceOf(Chunk::class, $info[3]);
        $this->assertEquals('abc123' , $keyInfo->getId());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($keyInfo));
    }


    /**
     * @return void
     */
    public function testMarshallingEmpty(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('ds:KeyInfo cannot be empty');

        $keyInfo = new KeyInfo([]);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $keyInfo = KeyInfo::fromXML($this->document->documentElement);
        $this->assertEquals('abc123', $keyInfo->getId());

        $info = $keyInfo->getInfo();
        $this->assertCount(4, $info);
        $this->assertInstanceOf(KeyName::class, $info[0]);
        $this->assertInstanceOf(X509Data::class, $info[1]);
        $this->assertInstanceOf(Chunk::class, $info[2]);
        $this->assertInstanceOf(Chunk::class, $info[3]);
        $this->assertEquals('abc123' , $keyInfo->getId());
    }


    /**
     * @return void
     */
    public function testUnmarshallingEmpty(): void
    {
        $document = DOMDocumentFactory::fromString('<ds:KeyInfo xmlns:ds="' . KeyInfo::NS . '"/>');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('ds:KeyInfo cannot be empty');

        $keyInfo = KeyInfo::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(KeyInfo::fromXML($this->document->documentElement))))
        );
    }
}
