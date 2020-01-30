<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\Chunk;

/**
 * Class \SAML2\XML\ds\KeyInfoTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class KeyInfoTest extends \PHPUnit\Framework\TestCase
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
        $keyInfo = new KeyInfo(
            [
                new KeyName('testkey'),
            ],
            'abc123'
        );

        $doc = DOMDocumentFactory::fromString('<ds:KeySomething>Some unknown tag within the ds-namespace</ds:KeySomething>');
        $keyInfo->addInfo(new Chunk($doc->firstChild));

        $doc = DOMDocumentFactory::fromString('<some>Chunk</some>');
        $keyInfo->addInfo(new Chunk($doc->firstChild));

        $dsns = KeyInfo::NS;
        $this->assertEquals(<<<XML
<ds:KeyInfo xmlns:ds="{$dsns}" Id="abc123">
  <ds:KeyName>testkey</ds:KeyName>
  <ds:KeySomething>Some unknown tag within the ds-namespace</ds:KeySomething>
  <some>Chunk</some>
</ds:KeyInfo>
XML
            ,
            strval($keyInfo)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<ds:KeyInfo xmlns:ds="' . KeyInfo::NS . '" Id="abc123">'
                . '<ds:KeyName>testkey</ds:KeyName>'
                . 'This DOMNodeText should be discarded by KeyInfo::fromXML'
                . '<ds:X509Data>' . $this->certificate . '</ds:X509Data>'
                . '<ds:KeySomething>Some unknown tag within the ds-namespace</ds:KeySomething>'
                . '<some>chunk</some></ds:KeyInfo>'
        );

        $keyInfo = KeyInfo::fromXML($document->firstChild);
        $this->assertEquals('abc123', $keyInfo->getId());

        $info = $keyInfo->getInfo();
        $this->assertCount(4, $info); // Without the DOMNodeText
        $this->assertEquals('testkey', $info[0]->getName());
    }
}
