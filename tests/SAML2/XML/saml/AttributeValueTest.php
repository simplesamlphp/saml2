<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Constants as C_XSI;

use function dirname;
use function strval;

/**
 * Tests for AttributeValue elements.
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(AttributeValue::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AttributeValueTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AttributeValue::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AttributeValue.xml',
        );
    }


    // marshalling


    /**
     * Test creating an AttributeValue from scratch using an integer.
     */
    public function testMarshalling(): void
    {
        $av = new AttributeValue(2);
        $this->assertIsInt($av->getValue());
        $this->assertEquals(2, $av->getValue());
        $this->assertEquals('xs:integer', $av->getXsiType());

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($av),
        );
    }


    /**
     * Test creating an AttributeValue from scratch using a string.
     */
    public function testMarshallingString(): void
    {
        $av = new AttributeValue('value');

        $this->assertEquals('value', $av->getValue());
        $this->assertEquals('xs:string', $av->getXsiType());
    }


    /**
     * Test creating an AttributeValue from scratch using an integer.
     */
    public function testMarshallingInteger(): void
    {
        $av = new AttributeValue(3);

        $this->assertEquals(3, $av->getValue());
        $this->assertEquals('xs:integer', $av->getXsiType());

        $nssaml = C::NS_SAML;
        $nsxs = C_XSI::NS_XS;
        $nsxsi = C_XSI::NS_XSI;
        $xml = <<<XML
<saml:AttributeValue xmlns:saml="{$nssaml}" xmlns:xsi="{$nsxsi}" xmlns:xs="{$nsxs}" xsi:type="xs:integer">3</saml:AttributeValue>
XML;
        $this->assertEquals(
            $xml,
            strval($av),
        );
    }


    /**
     * Test creating an AttributeValue from scratch using an dateTime.
     */
    public function testMarshallingDateTime(): void
    {
        $av = new AttributeValue(new DateTimeImmutable("2024-04-04T04:44:44Z"));

        /** @var \DateTimeInterface $value */
        $value = $av->getValue();
        $this->assertEquals('2024-04-04T04:44:44Z', $value->format(C::DATETIME_FORMAT));
        $this->assertEquals('xs:dateTime', $av->getXsiType());

        $nssaml = C::NS_SAML;
        $nsxs = C_XSI::NS_XS;
        $nsxsi = C_XSI::NS_XSI;
        $xml = <<<XML
<saml:AttributeValue xmlns:saml="{$nssaml}" xmlns:xsi="{$nsxsi}" xmlns:xs="{$nsxs}" xsi:type="xs:dateTime">2024-04-04T04:44:44Z</saml:AttributeValue>
XML;
        $this->assertEquals(
            $xml,
            strval($av),
        );
    }


    /**
     */
    public function testMarshallingNull(): void
    {
        $av = new AttributeValue(null);
        $this->assertNull($av->getValue());
        $this->assertEquals('xs:nil', $av->getXsiType());

        $nssaml = C::NS_SAML;
        $nsxsi = C_XSI::NS_XSI;
        $xml = <<<XML
<saml:AttributeValue xmlns:saml="{$nssaml}" xmlns:xsi="{$nsxsi}" xsi:nil="1"/>
XML;
        $this->assertEquals(
            $xml,
            strval($av),
        );
    }


    /**
     * Verifies that supplying an empty string as attribute value will
     * generate a tag with no content (instead of e.g. an empty tag).
     */
    public function testEmptyStringAttribute(): void
    {
        $av = new AttributeValue('');
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->textContent = '';

        $this->assertEquals('', $av->getValue());
        $this->assertEquals('xs:string', $av->getXsiType());
    }


    // unmarshalling


    /**
     * Verifies that we can create an AttributeValue containing a NameID from a DOMElement.
     *
     * @return void
     */
    public function testUnmarshallingNameID(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:persistent">abcd-some-value-xyz</saml:NameID>
</saml:AttributeValue>
XML
            ,
        );

        $av = AttributeValue::fromXML($document->documentElement);
        $value = $av->getValue();

        $this->assertInstanceOf(NameID::class, $value);

        $this->assertEquals('abcd-some-value-xyz', $value->getContent());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent', $value->getFormat());
        $this->assertXmlStringEqualsXmlString($document->saveXML(), $av->toXML()->ownerDocument?->saveXML());
    }


    /**
     * Serialize an EncryptedID and unserialize that again.
     * @return void
     */
    public function testSerializeEncryptedID(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
  <saml:EncryptedID>
    <xenc:EncryptedData Id="_4ea05f00adb06c642e0cb52f063e2570-1" Type="http://www.w3.org/2001/04/xmlenc#Element">
      <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes256-cbc"/>
      <ds:KeyInfo>
        <ds:RetrievalMethod URI="#_dc9043a7cbec55c6fcc61f1cf64cf868-1" Type="http://www.w3.org/2001/04/xmlenc#EncryptedKey"/>
      </ds:KeyInfo>
      <xenc:CipherData>
        <xenc:CipherValue>vErnRkA0oSmtQGamjZGa9RFN25SUx1UVLsLAOtopt7pyywTD7wu9pyocfD4HqduXCsvaiZpJykz11utZdvtJ0sOdm9oE+lAtNTUnKzGSNoSopGCzwNu5pqwhIEvWEWeilmJayAC2elpRYOnUs/rePxibz0Wbqa7BItLt6ZkKTtMkv0U0PpgGenF1pWzsahRtw6Y5tFq7xFQkG/z0Lz5rJ+IxExYXgB3LN6FBmVcB1ioahk2ovOwbLQ+lNAdqUMhpZx6fgdL2v7g4OYPK0rDgSALU3gU3dvU4hC/Kk9N5Rkw=</xenc:CipherValue>
      </xenc:CipherData>
    </xenc:EncryptedData>
    <xenc:EncryptedKey Id="_dc9043a7cbec55c6fcc61f1cf64cf868-1" Recipient="urn:nl-eid-gdi:1.0:DV:00000009900006840000:entities:9780">
      <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p">
        <ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>
      </xenc:EncryptionMethod>
      <ds:KeyInfo>
        <ds:KeyName>_b420654655d491b49555c698f80efb7bda3ac6ef</ds:KeyName>
      </ds:KeyInfo>
      <xenc:CipherData>
        <xenc:CipherValue>d5X+psMTEy9DWDoVotB5sPHdNpofv5BdPUleflhGfjbjGIfWbV9fK+jMkQ4cqwSWwmTGSQ8OO+lYA9IwZasnWygAu3dSSlYd+sd/m2waz83MrTBsTtZUzwy8N18tNMu4xB/tb45XPvis9agg6b2RdpDSS1m5BKK+MMKgX2ZYIqOW6cbXxl73YJHHHpcTi1TsI+tx6DLNWB2ku5wpS3cFB3c0Tws9qG+YBwTzfp1gFhzvBQDNxLWVjPKEWEw5Do1dp8f7jOPWq0sscz6DaTJH3RStwOSLF2vsayGZUgqNXyPHX12dFNGHiTXEWVYcuXsqG8sXJQyUmE5kYqN/D8NT1BIq50L6xIKkQGxNSlyAAkvkV87b1z6X2Fvk6Hvmx+eqauJX/BKknqgNZL/I5xfysDcNG5i0vA/kOVai4LvmdEoUSq1dIWcGsiW5pbM13R+DddkUegGcZctmFLcafBM2A3WztGclLWSJea+hh24YurcRGQVoOaZcYRmGVA0cYRQjfcSb3mvGxl01I9f27Hzv6/pXMWmEetpNhO4Pxy4GIwatJ7clW+s679f6N4+qepzbnxqjMLOmRPilJ0t9+wHYQ9wc2+NN4lssLNNz852rb5Rs9HDTvOyvtV7ew4HdMqGjXeJt545pQzSonuzCVVs67Nuhi/rmOktsav8tP0N8izE=</xenc:CipherValue>
      </xenc:CipherData>
      <xenc:ReferenceList>
        <xenc:DataReference URI="#_4ea05f00adb06c642e0cb52f063e2570-1"/>
      </xenc:ReferenceList>
    </xenc:EncryptedKey>
  </saml:EncryptedID>
</saml:AttributeValue>
XML
            ,
        );
        $document->formatOutput = true;

        $attributeValue = AttributeValue::fromXML($document->documentElement);

        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($attributeValue),
        );
    }
}
