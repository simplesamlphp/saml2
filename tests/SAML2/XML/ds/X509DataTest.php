<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\Chunk;

/**
 * Class \SAML2\XML\ds\X509DataTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class X509DataTest extends \PHPUnit\Framework\TestCase
{
    /** @var string */
    private const FRAMEWORK = 'vendor/simplesamlphp/simplesamlphp-test-framework';

    /** @var string */
    private $certificate;

    /** @var string */
    private $certData;


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

        $this->certData = openssl_x509_parse(
            file_get_contents(self::FRAMEWORK . '/certificates/pem/selfsigned.example.org.crt')
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $subject = DOMDocumentFactory::fromString(
            '<ds:X509SubjectName xmlns:ds="' . X509Data::NS . '">' . $this->certData['name'] . '</ds:X509SubjectName>'
        );

        $X509data = new X509Data(
            [
                new X509Certificate($this->certificate),
            ]
        );
        $X509data->addData(new Chunk($subject->firstChild));

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $X509data->toXML($document->firstChild);

        $X509DataElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'X509Data\' and namespace-uri()=\'' . X509Data::NS . '\']'
        );
        $this->assertCount(1, $X509DataElements);
        $X509DataElement = $X509DataElements[0];
        $this->assertEquals($this->certificate, $X509DataElement->childNodes[0]->textContent);
        $this->assertEquals($this->certData['name'], $X509DataElement->childNodes[1]->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<ds:X509CertData xmlns:ds="' . X509Data::NS . '">'
                . '<ds:X509UnknownTag>somevalue</ds:X509UnknownTag>'
                . '<ds:X509Certificate>' . $this->certificate . '</ds:X509Certificate>'
                . '<some>Chunk</some></ds:X509CertData>'
        );

        $X509data = X509Data::fromXML($document->firstChild);
        $this->assertEquals($this->certificate, $X509data->getData()[1]->getCertificate());
    }
}
