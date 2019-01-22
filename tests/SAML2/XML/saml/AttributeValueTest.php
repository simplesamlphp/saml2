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
}
