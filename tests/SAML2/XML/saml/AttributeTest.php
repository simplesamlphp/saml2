<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\md\AttributeTest
 */
class AttributeTest extends TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $attribute = new Attribute();
        $attribute->setName('TheName');
        $attribute->setNameFormat('TheNameFormat');
        $attribute->setFriendlyName('TheFriendlyName');
        $attribute->setAttributeValue([
            new AttributeValue('FirstValue'),
            new AttributeValue('SecondValue'),
        ]);

        $document = DOMDocumentFactory::fromString('<root />');
        $attributeElement = $attribute->toXML($document->firstChild);

        $xpCache = XPath::getXPath($attributeElement);
        $attributeElements = XPath::xpQuery($attributeElement, '/root/saml_assertion:Attribute', $xpCache);
        $this->assertCount(1, $attributeElements);
        /** @var \DOMElement $attributeElement */
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
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Attribute xmlns:saml="{$samlNamespace}" Name="TheName" NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
    <saml:AttributeValue>FirstValue</saml:AttributeValue>
    <saml:AttributeValue>SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
        );

        $attribute = new Attribute($document->firstChild);
        $this->assertEquals('TheName', $attribute->getName());
        $this->assertEquals('TheNameFormat', $attribute->getNameFormat());
        $this->assertEquals('TheFriendlyName', $attribute->getFriendlyName());
        $this->assertCount(2, $attribute->getAttributeValue());
        $this->assertEquals('FirstValue', strval($attribute->getAttributeValue()[0]));
        $this->assertEquals('SecondValue', strval($attribute->getAttributeValue()[1]));
    }


    /**
     * @return void
     */
    public function testUnmarshallingFailure(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Attribute xmlns:saml="{$samlNamespace}" NameFormat="TheNameFormat" FriendlyName="TheFriendlyName">
    <saml:AttributeValue>FirstValue</saml:AttributeValue>
    <saml:AttributeValue>SecondValue</saml:AttributeValue>
</saml:Attribute>
XML
        );
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing Name on Attribute.');
        new Attribute($document->firstChild);
    }
}
