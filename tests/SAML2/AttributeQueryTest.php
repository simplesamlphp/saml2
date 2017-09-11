<?php

namespace SAML2;

/**
 * Class \SAML2\AttributeQueryTest
 */
class AttributeQueryTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $attributeQuery = new AttributeQuery();
        $attributeQuery->setNameID(array('Value' => 'NameIDValue'));
        $attributeQuery->setAttributes(
            array(
                'test1' => array(
                    'test1_attrv1',
                    'test1_attrv2',
                ),
                'test2' => array(
                    'test2_attrv1',
                    'test2_attrv2',
                    'test2_attrv3',
                ),
                'test3' => array(),
            )
        );
        $attributeQueryElement = $attributeQuery->toUnsignedXML();

        // Test Attribute Names
        $attributes = Utils::xpQuery($attributeQueryElement, './saml_assertion:Attribute');
        $this->assertCount(3, $attributes);
        $this->assertEquals('test1', $attributes[0]->getAttribute('Name'));
        $this->assertEquals('test2', $attributes[1]->getAttribute('Name'));
        $this->assertEquals('test3', $attributes[2]->getAttribute('Name'));

        // Test Attribute Values for Attribute 1
        $av1 = Utils::xpQuery($attributes[0], './saml_assertion:AttributeValue');
        $this->assertCount(2, $av1);
        $this->assertEquals('test1_attrv1', $av1[0]->textContent);
        $this->assertEquals('test1_attrv2', $av1[1]->textContent);

        // Test Attribute Values for Attribute 2
        $av2 = Utils::xpQuery($attributes[1], './saml_assertion:AttributeValue');
        $this->assertCount(3, $av2);
        $this->assertEquals('test2_attrv1', $av2[0]->textContent);
        $this->assertEquals('test2_attrv2', $av2[1]->textContent);
        $this->assertEquals('test2_attrv3', $av2[2]->textContent);

        // Test Attribute Values for Attribute 3
        $av3 = Utils::xpQuery($attributes[2], './saml_assertion:AttributeValue');
        $this->assertCount(0, $av3);
    }

    public function testUnmarshalling()
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
        $this->assertEquals('https://example.org/', $aq->getIssuer());

        $attributes = array_keys($aq->getAttributes());
        $this->assertCount(3, $attributes);
        $this->assertEquals('urn:oid:1.3.6.1.4.1.5923.1.1.1.7', $attributes[0]);
        $this->assertEquals('urn:oid:2.5.4.4', $attributes[1]);
        $this->assertEquals('urn:oid:2.16.840.1.113730.3.1.39', $attributes[2]);

        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:uri', $aq->getAttributeNameFormat());
    }

    public function testAttributeNameFormat()
    {
        $fmt_uri = 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri';

        $attributeQuery = new AttributeQuery();
        $attributeQuery->setNameID(array('Value' => 'NameIDValue'));
        $attributeQuery->setAttributes(
            array(
                'test1' => array(
                    'test1_attrv1',
                    'test1_attrv2',
                ),
                'test2' => array(
                    'test2_attrv1',
                    'test2_attrv2',
                    'test2_attrv3',
                ),
                'test3' => array(),
            )
        );
        $attributeQuery->setAttributeNameFormat($fmt_uri);
        $attributeQueryElement = $attributeQuery->toUnsignedXML();

        // Test Attribute Names
        $attributes = Utils::xpQuery($attributeQueryElement, './saml_assertion:Attribute');
        $this->assertCount(3, $attributes);
        $this->assertEquals('test1', $attributes[0]->getAttribute('Name'));
        $this->assertEquals($fmt_uri, $attributes[0]->getAttribute('NameFormat'));
        $this->assertEquals('test2', $attributes[1]->getAttribute('Name'));
        $this->assertEquals($fmt_uri, $attributes[1]->getAttribute('NameFormat'));
        $this->assertEquals('test3', $attributes[2]->getAttribute('Name'));
        $this->assertEquals($fmt_uri, $attributes[2]->getAttribute('NameFormat'));

        // Sanity check: test if values are still ok
        $av1 = Utils::xpQuery($attributes[0], './saml_assertion:AttributeValue');
        $this->assertCount(2, $av1);
        $this->assertEquals('test1_attrv1', $av1[0]->textContent);
        $this->assertEquals('test1_attrv2', $av1[1]->textContent);
    }

    public function testNoNameFormatDefaultsToUnspecified()
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
        $this->assertEquals('https://example.org/', $aq->getIssuer());

        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified', $aq->getAttributeNameFormat());
    }

    public function testMultiNameFormatDefaultsToUnspecified()
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
        $this->assertEquals('https://example.org/', $aq->getIssuer());

        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:attrname-format:unspecified', $aq->getAttributeNameFormat());
    }

    /**
     * Each specified attribute requires a Name element, otherwise exception
     * is thrown.
     */
    public function testMissingNameOnAttributeThrowsException()
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

        $this->setExpectedException('Exception', 'Missing name on <saml:Attribute> element.');
        $aq = new AttributeQuery($document->firstChild);
    }

}
