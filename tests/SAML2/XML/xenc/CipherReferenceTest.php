<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\xenc;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDsig;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\xenc\CipherReferenceTest
 *
 * @covers \SimpleSAML\SAML2\XML\xenc\AbstractXencElement
 * @covers \SimpleSAML\SAML2\XML\xenc\AbstractReference
 * @covers \SimpleSAML\SAML2\XML\xenc\CipherReference
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class CipherReferenceTest extends TestCase
{
    /** @var \DOMDocument $document */
    private DOMDocument $document;

    /** @var \SAML2\XML\Chunk $reference */
    private Chunk $reference;


    /**
     * @return void
     */
    public function setup(): void
    {
        $dsNamespace = XMLSecurityDSig::XMLDSIGNS;

        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/xenc_CipherReference.xml'
        );

        $this->reference = new Chunk(DOMDocumentFactory::fromString(<<<XML
 <ds:Transforms xmlns:ds="{$dsNamespace}">
    <ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">
      <ds:XPath xmlns:xenc="http://www.w3.org/2001/04/xmlenc#">
        self::xenc:CipherValue[@Id="example1"]
      </ds:XPath>
    </ds:Transform>
  </ds:Transforms>
XML
        )->documentElement);
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $cipherReference = new CipherReference('#Cipher_VALUE_ID', [$this->reference]);

        $this->assertEquals('#Cipher_VALUE_ID', $cipherReference->getURI());

        $references = $cipherReference->getReferences();
        $this->assertCount(1, $references);
        $this->assertEquals($this->reference, $references[0]);

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($cipherReference)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $cipherReference = CipherReference::fromXML($this->document->documentElement);

        $this->assertEquals('#Cipher_VALUE_ID', $cipherReference->getURI());

        $references = $cipherReference->getReferences();
        $this->assertCount(1, $references);
        $this->assertEquals($this->reference, $references[0]);

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($cipherReference)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(CipherReference::fromXML($this->document->documentElement))))
        );
    }
}
