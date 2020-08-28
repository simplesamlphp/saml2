<?php

declare(strict_types=1);

namespace SAML2\XML\xenc;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDsig;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;

/**
 * Class \SAML2\XML\xenc\CipherDataTest
 *
 * @covers \SAML2\XML\xenc\CipherData
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class CipherDataTest extends TestCase
{
    /** @var \DOMDocument $documentCipherValue */
    private $documentCipherValue;

    /** @var \DOMDocument $documentCipherReference */
    private $documentCipherReference;

    /** @var \SAML2\Chunk $reference */
    private $reference;


    /**
     * @return void
     */
    public function setup(): void
    {
        $xencNamespace = Constants::NS_XENC;
        $dsNamespace = XMLSecurityDSig::XMLDSIGNS;

        $this->documentCipherValue = DOMDocumentFactory::fromString(<<<XML
<xenc:CipherData xmlns:xenc="{$xencNamespace}">
  <xenc:CipherValue>c29tZSB0ZXh0</xenc:CipherValue>
</xenc:CipherData>
XML
        );

        $this->documentCipherReference = DOMDocumentFactory::fromString(<<<XML
<xenc:CipherData xmlns:xenc="{$xencNamespace}">
  <xenc:CipherReference URI="#Cipher_VALUE_ID">
    <ds:Transforms xmlns:ds="{$dsNamespace}">
      <ds:Transform Algorithm="http://www.w3.org/TR/1999/REC-xpath-19991116">
        <ds:XPath xmlns:xenc="http://www.w3.org/2001/04/xmlenc#">
          self::xenc:CipherValue[@Id="example1"]
        </ds:XPath>
      </ds:Transform>
    </ds:Transforms>
  </xenc:CipherReference>
</xenc:CipherData>
XML
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
    public function testMarshallingCipherValue(): void
    {
        $cipherData = new CipherData('c29tZSB0ZXh0');

        $this->assertEquals('c29tZSB0ZXh0', $cipherData->getCipherValue());

        $this->assertEquals(
            $this->documentCipherValue->saveXML($this->documentCipherValue->documentElement),
            strval($cipherData)
        );
    }


    /**
     * @return void
     */
    public function testMarshallingCipherReference(): void
    {
        $cipherData = new CipherData(null, new CipherReference('#Cipher_VALUE_ID', [$this->reference]));

        $cipherReference = $cipherData->getCipherReference();
        $this->assertEquals('#Cipher_VALUE_ID', $cipherReference->getURI());
        $this->assertEquals($this->reference, $cipherReference->getReferences()[0]);

        $this->assertEquals(
            $this->documentCipherReference->saveXML($this->documentCipherReference->documentElement),
            strval($cipherData)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshallingCipherValue(): void
    {
        $cipherData = CipherData::fromXML($this->documentCipherValue->documentElement);

        $this->assertEquals('c29tZSB0ZXh0', $cipherData->getCipherValue());

        $this->assertEquals(
            $this->documentCipherValue->saveXML($this->documentCipherValue->documentElement),
            strval($cipherData)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshallingCipherReference(): void
    {
        $cipherData = CipherData::fromXML($this->documentCipherReference->documentElement);

        $cipherReference = $cipherData->getCipherReference();
        $this->assertEquals('#Cipher_VALUE_ID', $cipherReference->getURI());
        $this->assertEquals($this->reference, $cipherReference->getReferences()[0]);

        $this->assertEquals(
            $this->documentCipherReference->saveXML($this->documentCipherReference->documentElement),
            strval($cipherData)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->documentCipherValue->saveXML($this->documentCipherValue->documentElement),
            strval(unserialize(serialize(CipherData::fromXML($this->documentCipherValue->documentElement))))
        );
        $this->assertEquals(
            $this->documentCipherReference->saveXML($this->documentCipherReference->documentElement),
            strval(unserialize(serialize(CipherData::fromXML($this->documentCipherReference->documentElement))))
        );
    }
}
