<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AttributeQuery;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\TooManyElementsException;

use function dirname;
use function strval;

/**
 * Class \SAML2\AttributeQueryTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\AttributeQuery
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSubjectQuery
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class AttributeQueryTest extends TestCase
{
    use SerializableElementTestTrait;


    /**
     */
    public function setup(): void
    {
        $this->testedClass = AttributeQuery::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_AttributeQuery.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID('urn:example:subject', null, null, C::NAMEID_UNSPECIFIED);

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
            ],
            new Issuer('https://example.org/', null, null, C::NAMEID_ENTITY),
            'aaf23196-1773-2113-474a-fe114412ab72',
            1504698567
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($attributeQuery)
        );
    }


    public function testUnmarshalling(): void
    {
        $aq = AttributeQuery::fromXML($this->xmlRepresentation->documentElement);
        /** @psalm-var \SimpleSAML\SAML2\XML\saml\Issuer $issuer */
        $issuer = $aq->getIssuer();

        $subject = $aq->getSubject();
        /** @psalm-var \SimpleSAML\SAML2\XML\saml\NameID $identifier */
        $identifier = $subject->getIdentifier();

        // Sanity check
        $this->assertEquals('https://example.org/', $issuer->getContent());
        $this->assertEquals('urn:example:subject', $identifier->getContent());

        $attributes = $aq->getAttributes();
        $this->assertCount(4, $attributes);
        $this->assertEquals('test1', $attributes[0]->getName());
        $this->assertEquals('test2', $attributes[1]->getName());
        $this->assertEquals('test3', $attributes[2]->getName());
        $this->assertEquals('test4', $attributes[3]->getName());
    }


    public function testAttributeNameFormat(): void
    {
        $nameId = new NameID('NameIDValue');
        $attributeQuery = new AttributeQuery(
            new Subject($nameId),
            [
                new Attribute(
                    'test1',
                    C::NAMEFORMAT_URI,
                    null,
                    [
                        new AttributeValue('test1_attrv1'),
                        new AttributeValue('test1_attrv2')
                    ]
                ),
                new Attribute(
                    'test2',
                    C::NAMEFORMAT_URI,
                    null,
                    [
                        new AttributeValue('test2_attrv1'),
                        new AttributeValue('test2_attrv2'),
                        new AttributeValue('test2_attrv3')
                    ]
                ),
                new Attribute('test3', C::NAMEFORMAT_URI)
            ]
        );
        $attributeQueryElement = $attributeQuery->toXML();

        // Test Attribute Names
        /** @psalm-var \DOMElement[] $attributes */
        $xpCache = XPath::getXPath($attributeQueryElement);
        $attributes = XPath::xpQuery($attributeQueryElement, './saml_assertion:Attribute', $xpCache);
        $this->assertCount(3, $attributes);
        $this->assertEquals('test1', $attributes[0]->getAttribute('Name'));
        $this->assertEquals(C::NAMEFORMAT_URI, $attributes[0]->getAttribute('NameFormat'));
        $this->assertEquals('test2', $attributes[1]->getAttribute('Name'));
        $this->assertEquals(C::NAMEFORMAT_URI, $attributes[1]->getAttribute('NameFormat'));
        $this->assertEquals('test3', $attributes[2]->getAttribute('Name'));
        $this->assertEquals(C::NAMEFORMAT_URI, $attributes[2]->getAttribute('NameFormat'));

        // Sanity check: test if values are still ok
        $av1 = XPath::xpQuery($attributes[0], './saml_assertion:AttributeValue', $xpCache);
        $this->assertCount(2, $av1);
        $this->assertEquals('test1_attrv1', $av1[0]->textContent);
        $this->assertEquals('test1_attrv2', $av1[1]->textContent);
    }


    public function testNoNameFormatDefaultsToUnspecified(): void
    {
        $xml = <<<XML
<samlp:AttributeQuery
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    Version="2.0"
    ID="aaf23196-1773-2113-474a-fe114412ab72"
    IssueInstant="2017-09-06T11:49:27Z">
  <saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
  <saml:Subject>
    <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:example:subject</saml:NameID>
  </saml:Subject>
  <saml:Attribute Name="urn:oid:2.5.4.4" FriendlyName="sn"></saml:Attribute>
</samlp:AttributeQuery>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $aq = AttributeQuery::fromXML($document->documentElement);
        /** @psalm-var \SimpleSAML\SAML2\XML\saml\Issuer $issuer */
        $issuer = $aq->getIssuer();

        // Sanity check
        $this->assertEquals('https://example.org/', $issuer->getContent());
    }


    public function testMultiNameFormatDefaultsToUnspecified(): void
    {
        $xml = <<<XML
<samlp:AttributeQuery
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    Version="2.0"
    ID="aaf23196-1773-2113-474a-fe114412ab72"
    IssueInstant="2017-09-06T11:49:27Z">
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
      NameFormat="urn:example:some"
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
        /** @psalm-var \SimpleSAML\SAML2\XML\saml\Issuer $issuer */
        $issuer = $aq->getIssuer();

        // Sanity check
        $this->assertEquals('https://example.org/', $issuer->getContent());
    }


    /**
     * Each specified attribute requires a Name element, otherwise exception
     * is thrown.
     */
    public function testMissingNameOnAttributeThrowsException(): void
    {
        $xml = <<<XML
<samlp:AttributeQuery
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    Version="2.0"
    ID="aaf23196-1773-2113-474a-fe114412ab72"
    IssueInstant="2017-09-06T11:49:27Z">
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
        AttributeQuery::fromXML($document->documentElement);
    }


    public function testNoSubjectThrowsException(): void
    {
        $xml = <<<XML
<samlp:AttributeQuery
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    Version="2.0"
    ID="aaf23196-1773-2113-474a-fe114412ab72"
    IssueInstant="2017-09-06T11:49:27Z">
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
        AttributeQuery::fromXML($document->documentElement);
    }


    public function testTooManySubjectsThrowsException(): void
    {
        $xml = <<<XML
<samlp:AttributeQuery
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    Version="2.0"
    ID="aaf23196-1773-2113-474a-fe114412ab72"
    IssueInstant="2017-09-06T11:49:27Z">
  <saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
  <saml:Subject>
    <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:subject</saml:NameID>
  </saml:Subject>
  <saml:Subject>
    <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:another:subject</saml:NameID>
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
        AttributeQuery::fromXML($document->documentElement);
    }


    public function testNoNameIDinSubjectThrowsException(): void
    {
        $xml = <<<XML
<samlp:AttributeQuery
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    Version="2.0"
    ID="aaf23196-1773-2113-474a-fe114412ab72"
    IssueInstant="2017-09-06T11:49:27Z">
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
        $this->expectExceptionMessage(
            'A <saml:Subject> not containing <saml:SubjectConfirmation> should provide exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>'
        );

        AttributeQuery::fromXML($document->documentElement);
    }


    public function testTooManyNameIDsThrowsException(): void
    {
        $xml = <<<XML
<samlp:AttributeQuery
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    Version="2.0"
    ID="aaf23196-1773-2113-474a-fe114412ab72"
    IssueInstant="2017-09-06T11:49:27Z">
  <saml:Issuer Format="urn:oasis:names:tc:SAML:2.0:nameid-format:entity">https://example.org/</saml:Issuer>
  <saml:Subject>
    <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:subject</saml:NameID>
    <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">urn:another:subject</saml:NameID>
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
        AttributeQuery::fromXML($document->documentElement);
    }
}
