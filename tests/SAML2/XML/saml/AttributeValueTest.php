<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\saml\Attribute;
use SAML2\XML\saml\AttributeValue;

/**
 * Class \SAML2\XML\md\AttributeTest
 */
class AttributeValueTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Verifies that supplying an empty string as attribute value will
     * generate a tag with no content (instead of e.g. an empty tag).
     * @return void
     */
    public function testEmptyStringAttribute() : void
    {
        $attribute = new Attribute();
        $attribute->setName('TheName');
        $attribute->setNameFormat('TheNameFormat');
        $attribute->setFriendlyName('TheFriendlyName');
        $attribute->setAttributeValue([
            new AttributeValue(""),
        ]);

        $document = DOMDocumentFactory::fromString('<root />');
        $returnedStructure = $attribute->toXML($document->firstChild);

        $expectedStructureDocument = new \DOMDocument();
        $expectedStructureDocument->loadXML(<<<ATTRIBUTEVALUE
<saml:Attribute xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" Name="TheName"
 NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
  <saml:AttributeValue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xs="http://www.w3.org/2001/XMLSchema" xsi:type="xs:string"></saml:AttributeValue>
</saml:Attribute>
ATTRIBUTEVALUE
        );
        $expectedStructure = $expectedStructureDocument->documentElement;

        $this->assertEqualXMLStructure($expectedStructure, $returnedStructure);
        $this->assertEquals("", $attribute->getAttributeValue()[0]->getString());
    }


    /**
     * Verifies that we can create an AttributeValue from a DOMElement.
     * @return void
     */
    public function testCreateAttributeFromDOMElement() : void
    {
        $attribute = new Attribute();
        $attribute->setName('TheName');
        $attribute->setNameFormat('TheNameFormat');
        $attribute->setFriendlyName('TheFriendlyName');

        $element = new \DOMDocument();
        $element->loadXML(<<<ATTRIBUTEVALUE
<NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">urn:collab:person:surftest.nl:example</NameID>
ATTRIBUTEVALUE
        );

        $attribute->setAttributeValue([
            new AttributeValue($element->documentElement),
        ]);

        $document = DOMDocumentFactory::fromString('<root />');
        $returnedStructure = $attribute->toXML($document->firstChild);

        $expectedStructureDocument = new \DOMDocument();
        $expectedStructureDocument->loadXML(<<<ATTRIBUTEXML
<saml:Attribute xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
  Name="TheName" NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
  <saml:AttributeValue>
    <NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">urn:collab:person:surftest.nl:example</NameID>
  </saml:AttributeValue>
</saml:Attribute>
ATTRIBUTEXML
        );
        $expectedStructure = $expectedStructureDocument->documentElement;

        $this->assertEqualXMLStructure($expectedStructure, $returnedStructure);
        $this->assertEquals("urn:collab:person:surftest.nl:example", $attribute->getAttributeValue()[0]->getString());
    }


    /**
     * Serialize an AttributeValue and Unserialize that again.
     * @return void
     */
    public function testSerialize() : void
    {
        $av1 = new AttributeValue("Aap:noot:mies");
        $ser = $av1->serialize();
        $av2 = new AttributeValue("Wim");
        $av2->unserialize($ser);

        $this->assertEquals("Aap:noot:mies", $av2->getString());

        $element = new \DOMDocument();
        $element->loadXML(<<<ATTRIBUTEVALUE
<NameID Format="urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified">urn:collab:person:surftest.nl:example</NameID>
ATTRIBUTEVALUE
        );

        $av3 = new AttributeValue($element->documentElement);
        $ser = $av3->serialize();
        $av4 = new AttributeValue("Wim");
        $av4->unserialize($ser);
        $this->assertEquals("urn:collab:person:surftest.nl:example", $av4->getString());
    }


    /**
     * Serialize an EncryptedID and unserialize that again.
     * @return void
     */
    public function testSerializeEncryptedID() : void
    {
        $source = '<saml:AttributeValue xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"><saml:EncryptedID xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" xmlns:ds="http://www.w3.org/2000/09/xmldsig#"><xenc:EncryptedData xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" Id="_4ea05f00adb06c642e0cb52f063e2570-1" Type="http://www.w3.org/2001/04/xmlenc#Element"><xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes256-cbc"/><ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#"><ds:RetrievalMethod Type="http://www.w3.org/2001/04/xmlenc#EncryptedKey" URI="#_dc9043a7cbec55c6fcc61f1cf64cf868-1"/></ds:KeyInfo><xenc:CipherData><xenc:CipherValue>vErnRkA0oSmtQGamjZGa9RFN25SUx1UVLsLAOtopt7pyywTD7wu9pyocfD4HqduXCsvaiZpJykz11utZdvtJ0sOdm9oE+lAtNTUnKzGSNoSopGCzwNu5pqwhIEvWEWeilmJayAC2elpRYOnUs/rePxibz0Wbqa7BItLt6ZkKTtMkv0U0PpgGenF1pWzsahRtw6Y5tFq7xFQkG/z0Lz5rJ+IxExYXgB3LN6FBmVcB1ioahk2ovOwbLQ+lNAdqUMhpZx6fgdL2v7g4OYPK0rDgSALU3gU3dvU4hC/Kk9N5Rkw=</xenc:CipherValue></xenc:CipherData></xenc:EncryptedData><xenc:EncryptedKey xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" Id="_dc9043a7cbec55c6fcc61f1cf64cf868-1" Recipient="urn:nl-eid-gdi:1.0:DV:00000009900006840000:entities:9780"><xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p"><ds:DigestMethod xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/></xenc:EncryptionMethod><ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#"><ds:KeyName>_b420654655d491b49555c698f80efb7bda3ac6ef</ds:KeyName></ds:KeyInfo><xenc:CipherData><xenc:CipherValue>d5X+psMTEy9DWDoVotB5sPHdNpofv5BdPUleflhGfjbjGIfWbV9fK+jMkQ4cqwSWwmTGSQ8OO+lYA9IwZasnWygAu3dSSlYd+sd/m2waz83MrTBsTtZUzwy8N18tNMu4xB/tb45XPvis9agg6b2RdpDSS1m5BKK+MMKgX2ZYIqOW6cbXxl73YJHHHpcTi1TsI+tx6DLNWB2ku5wpS3cFB3c0Tws9qG+YBwTzfp1gFhzvBQDNxLWVjPKEWEw5Do1dp8f7jOPWq0sscz6DaTJH3RStwOSLF2vsayGZUgqNXyPHX12dFNGHiTXEWVYcuXsqG8sXJQyUmE5kYqN/D8NT1BIq50L6xIKkQGxNSlyAAkvkV87b1z6X2Fvk6Hvmx+eqauJX/BKknqgNZL/I5xfysDcNG5i0vA/kOVai4LvmdEoUSq1dIWcGsiW5pbM13R+DddkUegGcZctmFLcafBM2A3WztGclLWSJea+hh24YurcRGQVoOaZcYRmGVA0cYRQjfcSb3mvGxl01I9f27Hzv6/pXMWmEetpNhO4Pxy4GIwatJ7clW+s679f6N4+qepzbnxqjMLOmRPilJ0t9+wHYQ9wc2+NN4lssLNNz852rb5Rs9HDTvOyvtV7ew4HdMqGjXeJt545pQzSonuzCVVs67Nuhi/rmOktsav8tP0N8izE=</xenc:CipherValue></xenc:CipherData><xenc:ReferenceList><xenc:DataReference URI="#_4ea05f00adb06c642e0cb52f063e2570-1"/></xenc:ReferenceList></xenc:EncryptedKey></saml:EncryptedID></saml:AttributeValue>';
        $element = new \DOMDocument();
        $element->loadXML($source);

        $av = new AttributeValue($element->documentElement);
        /** @var AttributeValue */
        $av = unserialize(serialize($av));

        // Assert that saveXML returns the complete string.
        // We don't use assertEquals, because the saveXML operation also includes a XML header.
        $this->assertStringContainsString(
            $source,
            $av->getElement()->ownerDocument->saveXML()
        );
    }
}
