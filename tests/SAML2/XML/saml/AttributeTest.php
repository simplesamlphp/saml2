<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\saml\Attribute;
use SAML2\XML\saml\AttributeValue;

/**
 * Class \SAML2\XML\md\AttributeTest
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $attribute = new Attribute();
        $attribute->Name = 'TheName';
        $attribute->NameFormat = 'TheNameFormat';
        $attribute->FriendlyName = 'TheFriendlyName';
        $attribute->AttributeValue = array(
            new AttributeValue('FirstValue'),
            new AttributeValue('SecondValue'),
        );

        $document = DOMDocumentFactory::fromString('<root />');
        $attributeElement = $attribute->toXML($document->firstChild);

        $attributeElements = Utils::xpQuery($attributeElement, '/root/saml_assertion:Attribute');
        $this->assertCount(1, $attributeElements);
        $attributeElement = $attributeElements[0];

        $this->assertEquals('TheName', $attributeElement->getAttribute('Name'));
        $this->assertEquals('TheNameFormat', $attributeElement->getAttribute('NameFormat'));
        $this->assertEquals('TheFriendlyName', $attributeElement->getAttribute('FriendlyName'));
    }

    public function testUnmarshalling()
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Attribute xmlns:saml="{$samlNamespace}" Name="TheName" NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
    <saml:AttributeValue>FirstValue</saml:AttributeValue>
    <saml:AttributeValue>SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
        );

        $attribute = new Attribute($document->firstChild);
        $this->assertEquals('TheName', $attribute->Name);
        $this->assertEquals('TheNameFormat', $attribute->NameFormat);
        $this->assertEquals('TheFriendlyName', $attribute->FriendlyName);
        $this->assertCount(2, $attribute->AttributeValue);
        $this->assertEquals('FirstValue', (string)$attribute->AttributeValue[0]);
        $this->assertEquals('SecondValue', (string)$attribute->AttributeValue[1]);
    }

    public function testUnmarshallingFailure()
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Attribute xmlns:saml="{$samlNamespace}" NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
    <saml:AttributeValue>FirstValue</saml:AttributeValue>
    <saml:AttributeValue>SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
        );
        $this->setExpectedException('Exception', 'Missing Name on Attribute.');
        new Attribute($document->firstChild);
    }
}
