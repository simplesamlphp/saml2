<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\AttributeQuery;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\AttributeQueryTest
 */
class AttributeQueryTest extends TestCase
{
    public function testMarshalling(): void
    {
        $attributeQuery = new AttributeQuery();
        $nameId = new NameID();
        $nameId->setValue('NameIDValue');
        $attributeQuery->setNameID($nameId);
        $attributeQuery->setAttributes(
            [
                'test1' => [
                    'test1_attrv1',
                    'test1_attrv2',
                ],
                'test2' => [
                    'test2_attrv1',
                    'test2_attrv2',
                    'test2_attrv3',
                ],
                'test3' => [],
                'test4' => [ 4, 23 ],
            ]
        );
        $attributeQueryElement = $attributeQuery->toUnsignedXML();

        // Test Attribute Names
        $xpCache = XPath::getXPath($attributeQueryElement);
        $attributes = XPath::xpQuery($attributeQueryElement, './saml_assertion:Attribute', $xpCache);
        $this->assertCount(4, $attributes);
        $this->assertEquals('test1', $attributes[0]->getAttribute('Name'));
        $this->assertEquals('test2', $attributes[1]->getAttribute('Name'));
        $this->assertEquals('test3', $attributes[2]->getAttribute('Name'));

        // Test Attribute Values for Attribute 1
        $xpCache = XPath::getXPath($attributes[0]);
        $av1 = XPath::xpQuery($attributes[0], './saml_assertion:AttributeValue', $xpCache);
        $this->assertCount(2, $av1);
        $this->assertEquals('test1_attrv1', $av1[0]->textContent);
        $this->assertEquals('test1_attrv2', $av1[1]->textContent);

        // Test Attribute Values for Attribute 2
        $xpCache = XPath::getXPath($attributes[1]);
        $av2 = XPath::xpQuery($attributes[1], './saml_assertion:AttributeValue', $xpCache);
        $this->assertCount(3, $av2);
        $this->assertEquals('xs:string', $av2[0]->getAttribute('xsi:type'));
        $this->assertEquals('test2_attrv1', $av2[0]->textContent);
        $this->assertEquals('xs:string', $av2[1]->getAttribute('xsi:type'));
        $this->assertEquals('test2_attrv2', $av2[1]->textContent);
        $this->assertEquals('xs:string', $av2[2]->getAttribute('xsi:type'));
        $this->assertEquals('test2_attrv3', $av2[2]->textContent);

        // Test Attribute Values for Attribute 3
        $xpCache = XPath::getXPath($attributes[2]);
        $av3 = XPath::xpQuery($attributes[2], './saml_assertion:AttributeValue', $xpCache);
        $this->assertCount(0, $av3);

        // Test Attribute Values for Attribute 3
        $xpCache = XPath::getXPath($attributes[3]);
        $av3 = XPath::xpQuery($attributes[3], './saml_assertion:AttributeValue', $xpCache);
        $this->assertCount(2, $av3);
        $this->assertEquals('4', $av3[0]->textContent);
        $this->assertEquals('xs:integer', $av3[0]->getAttribute('xsi:type'));
        $this->assertEquals('23', $av3[1]->textContent);
        $this->assertEquals('xs:integer', $av3[1]->getAttribute('xsi:type'));
    }


    public function testUnmarshalling(): void
    {
        $xml = <<<XML
  <samlp:AttributeQuery xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="aaf23196-1773-2113-474a-fe114412ab72" Version="2.0" IssueInstant="2017-09-06T11:49:27Z">
	<saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
	<saml:Subject>
	  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:example:subject</saml:NameID>
	</saml:Subject>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7"
	  FriendlyName="entitlements">
	</saml:Attribute>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  Name="urn:oid:2.5.4.4"
	  FriendlyName="sn">
	</saml:Attribute>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  Name="urn:oid:2.16.840.1.113730.3.1.39"
	  FriendlyName="preferredLanguage">
	</saml:Attribute>
</samlp:AttributeQuery>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $aq = new AttributeQuery($document->firstChild);

        // Sanity check
        $this->assertEquals('https://example.org/', $aq->getIssuer()->getValue());

        $nameid = $aq->getNameId();
        $this->assertInstanceOf(NameID::class, $nameid);
        $this->assertEquals('urn:example:subject', $nameid->getValue());

        $attributes = array_keys($aq->getAttributes());
        $this->assertCount(3, $attributes);
        $this->assertEquals('urn:oid:1.3.6.1.4.1.5923.1.1.1.7', $attributes[0]);
        $this->assertEquals('urn:oid:2.5.4.4', $attributes[1]);
        $this->assertEquals('urn:oid:2.16.840.1.113730.3.1.39', $attributes[2]);

        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:uri', $aq->getAttributeNameFormat());
    }


    public function testAttributeNameFormat(): void
    {
        $fmt_uri = 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri';

        $attributeQuery = new AttributeQuery();
        $nameId = new NameID();
        $nameId->setValue('NameIDValue');
        $attributeQuery->setNameID($nameId);
        $attributeQuery->setAttributes(
            [
                'test1' => [
                    'test1_attrv1',
                    'test1_attrv2',
                    ],
                'test2' => [
                    'test2_attrv1',
                    'test2_attrv2',
                    'test2_attrv3',
                    ],
                'test3' => [],
            ]
        );
        $attributeQuery->setAttributeNameFormat($fmt_uri);
        $attributeQueryElement = $attributeQuery->toUnsignedXML();

        // Test Attribute Names
        $xpCache = XPath::getXPath($attributeQueryElement);
        $attributes = XPath::xpQuery($attributeQueryElement, './saml_assertion:Attribute', $xpCache);
        $this->assertCount(3, $attributes);
        $this->assertEquals('test1', $attributes[0]->getAttribute('Name'));
        $this->assertEquals($fmt_uri, $attributes[0]->getAttribute('NameFormat'));
        $this->assertEquals('test2', $attributes[1]->getAttribute('Name'));
        $this->assertEquals($fmt_uri, $attributes[1]->getAttribute('NameFormat'));
        $this->assertEquals('test3', $attributes[2]->getAttribute('Name'));
        $this->assertEquals($fmt_uri, $attributes[2]->getAttribute('NameFormat'));

        // Sanity check: test if values are still ok
        $xpCache = XPath::getXPath($attributes[0]);
        $av1 = XPath::xpQuery($attributes[0], './saml_assertion:AttributeValue', $xpCache);
        $this->assertCount(2, $av1);
        $this->assertEquals('test1_attrv1', $av1[0]->textContent);
        $this->assertEquals('test1_attrv2', $av1[1]->textContent);
    }


    public function testNoNameFormatDefaultsToUnspecified(): void
    {
        $xml = <<<XML
  <samlp:AttributeQuery xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="aaf23196-1773-2113-474a-fe114412ab72" Version="2.0" IssueInstant="2017-09-06T11:49:27Z">
	<saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
	<saml:Subject>
	  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:example:subject</saml:NameID>
	</saml:Subject>
	<saml:Attribute
	  Name="urn:oid:2.5.4.4"
	  FriendlyName="sn">
	</saml:Attribute>
</samlp:AttributeQuery>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $aq = new AttributeQuery($document->firstChild);

        // Sanity check
        $this->assertEquals('https://example.org/', $aq->getIssuer()->getValue());

        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified', $aq->getAttributeNameFormat());
    }


    public function testMultiNameFormatDefaultsToUnspecified(): void
    {
        $xml = <<<XML
  <samlp:AttributeQuery xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="aaf23196-1773-2113-474a-fe114412ab72" Version="2.0" IssueInstant="2017-09-06T11:49:27Z">
	<saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
	<saml:Subject>
	  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:example:subject</saml:NameID>
	</saml:Subject>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7"
	  FriendlyName="entitlements">
	</saml:Attribute>
	<saml:Attribute
	  NameFormat="urn:example"
	  Name="urn:oid:2.5.4.4"
	  FriendlyName="sn">
	</saml:Attribute>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  Name="urn:oid:2.16.840.1.113730.3.1.39"
	  FriendlyName="preferredLanguage">
	</saml:Attribute>
</samlp:AttributeQuery>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $aq = new AttributeQuery($document->firstChild);

        // Sanity check
        $this->assertEquals('https://example.org/', $aq->getIssuer()->getValue());

        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified', $aq->getAttributeNameFormat());
    }


    /**
     * Each specified attribute requires a Name element, otherwise exception
     * is thrown.
     */
    public function testMissingNameOnAttributeThrowsException(): void
    {
        $xml = <<<XML
  <samlp:AttributeQuery xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="aaf23196-1773-2113-474a-fe114412ab72" Version="2.0" IssueInstant="2017-09-06T11:49:27Z">
	<saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
	<saml:Subject>
	  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:example:subject</saml:NameID>
	</saml:Subject>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7"
	  FriendlyName="entitlements">
	</saml:Attribute>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  FriendlyName="sn">
	</saml:Attribute>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  Name="urn:oid:2.16.840.1.113730.3.1.39"
	  FriendlyName="preferredLanguage">
	</saml:Attribute>
</samlp:AttributeQuery>
XML;
        $document = DOMDocumentFactory::fromString($xml);

        $this->expectException(Exception::class, 'Missing name on <saml:Attribute> element.');
        $aq = new AttributeQuery($document->firstChild);
    }


    public function testNoSubjectThrowsException(): void
    {
        $xml = <<<XML
  <samlp:AttributeQuery xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="aaf23196-1773-2113-474a-fe114412ab72" Version="2.0" IssueInstant="2017-09-06T11:49:27Z">
	<saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7"
	  FriendlyName="entitlements">
	</saml:Attribute>
</samlp:AttributeQuery>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->expectException(Exception::class, 'Missing subject in subject');
        $aq = new AttributeQuery($document->firstChild);
    }


    public function testTooManySubjectsThrowsException(): void
    {
        $xml = <<<XML
  <samlp:AttributeQuery xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="aaf23196-1773-2113-474a-fe114412ab72" Version="2.0" IssueInstant="2017-09-06T11:49:27Z">
	<saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
	<saml:Subject>
	  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:example:subject</saml:NameID>
	</saml:Subject>
	<saml:Subject>
	  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:example:another:subject</saml:NameID>
	</saml:Subject>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7"
	  FriendlyName="entitlements">
	</saml:Attribute>
</samlp:AttributeQuery>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->expectException(Exception::class, 'More than one <saml:Subject> in subject');
        $aq = new AttributeQuery($document->firstChild);
    }


    public function testNoNameIDinSubjectThrowsException(): void
    {
        $xml = <<<XML
  <samlp:AttributeQuery xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="aaf23196-1773-2113-474a-fe114412ab72" Version="2.0" IssueInstant="2017-09-06T11:49:27Z">
	<saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
	<saml:Subject>
	  <saml:something>example</saml:something>
	</saml:Subject>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7"
	  FriendlyName="entitlements">
	</saml:Attribute>
</samlp:AttributeQuery>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->expectException(Exception::class, 'Missing <saml:NameID> in <saml:Subject>');
        $aq = new AttributeQuery($document->firstChild);
    }


    public function testTooManyNameIDsThrowsException(): void
    {
        $xml = <<<XML
  <samlp:AttributeQuery xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" ID="aaf23196-1773-2113-474a-fe114412ab72" Version="2.0" IssueInstant="2017-09-06T11:49:27Z">
	<saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
	<saml:Subject>
	  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:example:subject</saml:NameID>
	  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:example:another:subject</saml:NameID>
	</saml:Subject>
	<saml:Attribute
	  NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
	  Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7"
	  FriendlyName="entitlements">
	</saml:Attribute>
</samlp:AttributeQuery>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->expectException(Exception::class, 'More than one <saml:NameID> in <saml:Subject>');
        $aq = new AttributeQuery($document->firstChild);
    }
}
