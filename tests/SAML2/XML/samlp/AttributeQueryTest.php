<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Exception\MissingElementException;
use SAML2\Exception\MissingAttributeException;
use SAML2\Exception\TooManyElementsException;
use SAML2\Utils;
use SAML2\XML\saml\Attribute;
use SAML2\XML\saml\AttributeValue;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use SAML2\XML\saml\Subject;

/**
 * Class \SAML2\AttributeQueryTest
 */
class AttributeQueryTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $samlpNamespace = AttributeQuery::NS;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:AttributeQuery xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="aaf23196-1773-2113-474a-fe114412ab72" Version="2.0" IssueInstant="2017-09-06T11:49:27Z">
  <saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
  <saml:Subject>
    <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:example:subject</saml:NameID>
  </saml:Subject>
  <saml:Attribute
    Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7"
    NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
    FriendlyName="entitlements"/>
  <saml:Attribute
    Name="urn:oid:2.5.4.4"
    NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
    FriendlyName="sn"/>
  <saml:Attribute
    Name="urn:oid:2.16.840.1.113730.3.1.39"
    NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
    FriendlyName="preferredLanguage"/>
</samlp:AttributeQuery>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID('NameIDValue');
        $attributeQuery = new AttributeQuery(
            new Subject($nameId),
            [
                new Attribute(
                    'test1',
                    null,
                    null,
                    [
                        new AttributeValue('test1_attrv1'),
                        new AttributeValue('test1_attrv2')
                    ]
                ),
                new Attribute(
                    'test2',
                    null,
                    null,
                    [
                        new AttributeValue('test2_attrv1'),
                        new AttributeValue('test2_attrv2'),
                        new AttributeValue('test2_attrv3')
                    ]
                ),
                new Attribute(
                    'test3'
                ),
                new Attribute(
                    'test4',
                    null,
                    null,
                    [
                        new AttributeValue(4),
                        new AttributeValue(23)
                    ]
                )
            ]
        );
        $attributeQueryElement = $attributeQuery->toXML();

        // Test Attribute Names
        $attributes = Utils::xpQuery($attributeQueryElement, './saml_assertion:Attribute');
        $this->assertCount(4, $attributes);
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

        // Test Attribute Values for Attribute 3
        $av3 = Utils::xpQuery($attributes[3], './saml_assertion:AttributeValue');
        $this->assertCount(2, $av3);
        $this->assertEquals('4', $av3[0]->textContent);
        $this->assertEquals('xs:integer', $av3[0]->getAttribute('xsi:type'));
        $this->assertEquals('23', $av3[1]->textContent);
        $this->assertEquals('xs:integer', $av3[1]->getAttribute('xsi:type'));
    }


    public function testUnmarshalling(): void
    {
        $aq = AttributeQuery::fromXML($this->document->documentElement);
        /** @psalm-var \SAML2\XML\saml\Issuer $issuer */
        $issuer = $aq->getIssuer();
        $subject = $aq->getSubject();
        /** @psalm-var \SAML2\XML\saml\NameID $identifier */
        $identifier = $subject->getIdentifier();

        // Sanity check
        $this->assertEquals('https://example.org/', $issuer->getValue());
        $this->assertEquals('urn:example:subject', $identifier->getValue());

        $attributes = $aq->getAttributes();
        $this->assertCount(3, $attributes);
        $this->assertEquals('urn:oid:1.3.6.1.4.1.5923.1.1.1.7', $attributes[0]->getName());
        $this->assertEquals('urn:oid:2.5.4.4', $attributes[1]->getName());
        $this->assertEquals('urn:oid:2.16.840.1.113730.3.1.39', $attributes[2]->getName());
    }


    public function testAttributeNameFormat(): void
    {
        $nameId = new NameID('NameIDValue');
        $attributeQuery = new AttributeQuery(
            new Subject($nameId),
            [
                new Attribute(
                    'test1',
                    Constants::NAMEFORMAT_URI,
                    null,
                    [
                        new AttributeValue('test1_attrv1'),
                        new AttributeValue('test1_attrv2')
                    ]
                ),
                new Attribute(
                    'test2',
                    Constants::NAMEFORMAT_URI,
                    null,
                    [
                        new AttributeValue('test2_attrv1'),
                        new AttributeValue('test2_attrv2'),
                        new AttributeValue('test2_attrv3')
                    ]
                ),
                new Attribute('test3', Constants::NAMEFORMAT_URI)
            ]
        );
        $attributeQueryElement = $attributeQuery->toXML();

        // Test Attribute Names
        $attributes = Utils::xpQuery($attributeQueryElement, './saml_assertion:Attribute');
        $this->assertCount(3, $attributes);
        $this->assertEquals('test1', $attributes[0]->getAttribute('Name'));
        $this->assertEquals(Constants::NAMEFORMAT_URI, $attributes[0]->getAttribute('NameFormat'));
        $this->assertEquals('test2', $attributes[1]->getAttribute('Name'));
        $this->assertEquals(Constants::NAMEFORMAT_URI, $attributes[1]->getAttribute('NameFormat'));
        $this->assertEquals('test3', $attributes[2]->getAttribute('Name'));
        $this->assertEquals(Constants::NAMEFORMAT_URI, $attributes[2]->getAttribute('NameFormat'));

        // Sanity check: test if values are still ok
        $av1 = Utils::xpQuery($attributes[0], './saml_assertion:AttributeValue');
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
        $aq = AttributeQuery::fromXML($document->documentElement);
        /** @psalm-var \SAML2\XML\saml\Issuer $issuer */
        $issuer = $aq->getIssuer();

        // Sanity check
        $this->assertEquals('https://example.org/', $issuer->getValue());
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
        $aq = AttributeQuery::fromXML($document->documentElement);
        /** @psalm-var \SAML2\XML\saml\Issuer $issuer */
        $issuer = $aq->getIssuer();

        // Sanity check
        $this->assertEquals('https://example.org/', $issuer->getValue());
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

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'Name\' attribute on saml:Attribute.');
        $aq = AttributeQuery::fromXML($document->documentElement);
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
        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('Missing subject in subject');
        $aq = AttributeQuery::fromXML($document->documentElement);
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
        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage('More than one <saml:Subject> in AttributeQuery');
        $aq = AttributeQuery::fromXML($document->documentElement);
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
        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage('A <saml:Subject> not containing <saml:SubjectConfirmation> should provide exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>');
        $aq = AttributeQuery::fromXML($document->documentElement);
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
        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage('More than one <saml:NameID> in <saml:Subject>');
        $aq = AttributeQuery::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(AttributeQuery::fromXML($this->document->documentElement))))
        );
    }
}
