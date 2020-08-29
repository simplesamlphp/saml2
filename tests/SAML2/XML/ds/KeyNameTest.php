<?php

declare(strict_types=1);

namespace \SimpleSAML\SAML2\XML\ds;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\ds\KeyNameTest
 *
 * @covers \SimpleSAML\SAML2\XML\ds\KeyName
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class KeyNameTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $ns = KeyName::NS;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<ds:KeyName xmlns:ds="{$ns}">testkey</ds:KeyName>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $keyName = new KeyName('testkey');

        $this->assertEquals('testkey', $keyName->getName());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($keyName));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $keyName = KeyName::fromXML($this->document->documentElement);

        $this->assertEquals('testkey', $keyName->getName());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(KeyName::fromXML($this->document->documentElement))))
        );
    }
}
