<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use Exception;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\AttributeTest
 */
class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $attribute = new Attribute(
            'TheName',
            'TheNameFormat',
            'TheFriendlyName',
            [
                new AttributeValue('FirstValue'),
                new AttributeValue('SecondValue')
            ]
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


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Attribute xmlns:saml="{$samlNamespace}" Name="TheName" NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
    <saml:AttributeValue>FirstValue</saml:AttributeValue>
    <saml:AttributeValue>SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
        );

        $attribute = Attribute::fromXML($document->firstChild);
        $this->assertEquals('TheName', $attribute->getName());
        $this->assertEquals('TheNameFormat', $attribute->getNameFormat());
        $this->assertEquals('TheFriendlyName', $attribute->getFriendlyName());
        $this->assertCount(2, $attribute->getAttributeValues());
        $this->assertEquals('FirstValue', strval($attribute->getAttributeValues()[0]));
        $this->assertEquals('SecondValue', strval($attribute->getAttributeValues()[1]));
    }


    /**
     * @return void
     */
    public function testUnmarshallingFailure(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Attribute xmlns:saml="{$samlNamespace}" NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
    <saml:AttributeValue>FirstValue</saml:AttributeValue>
    <saml:AttributeValue>SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
        );

        $this->expectException(Exception::class, 'Missing Name on Attribute.');
        Attribute::fromXML($document->firstChild);
    }
}
