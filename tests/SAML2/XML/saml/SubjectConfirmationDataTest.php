<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\SubjectConfirmationDataTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationDataTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        self::$testedClass = SubjectConfirmationData::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_SubjectConfirmationData.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $arbitrary = DOMDocumentFactory::fromString('<some>Arbitrary Element</some>');

        $attr1 = new XMLAttribute('urn:test:something', 'test', 'attr1', 'testval1');
        $attr2 = new XMLAttribute('urn:test:something', 'test', 'attr2', 'testval2');

        $subjectConfirmationData = new SubjectConfirmationData(
            new DateTimeImmutable('2001-04-19T04:25:21Z'),
            new DateTimeImmutable('2009-02-13T23:31:30Z'),
            C::ENTITY_SP,
            'SomeRequestID',
            '127.0.0.1',
            [
                new KeyInfo([new KeyName('SomeKey')]),
                new Chunk($arbitrary->documentElement),
            ],
            [$attr1, $attr2]
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($subjectConfirmationData),
        );
    }


    /**
     */
    public function testMarshallingWithNonIPAddress(): void
    {
        $arbitrary = DOMDocumentFactory::fromString('<some>Arbitrary Element</some>');

        $attr1 = new XMLAttribute('urn:test:something', 'test', 'attr1', 'testval1');
        $attr2 = new XMLAttribute('urn:test:something', 'test', 'attr2', 'testval2');

        $subjectConfirmationData = new SubjectConfirmationData(
            new DateTimeImmutable('2001-04-19T04:25:21Z'),
            new DateTimeImmutable('2009-02-13T23:31:30Z'),
            C::ENTITY_SP,
            'SomeRequestID',
            'non-IP',
            [
                new KeyInfo([new KeyName('SomeKey')]),
                new Chunk($arbitrary->documentElement),
            ],
            [$attr1, $attr2],
        );

        $this->assertEquals(
            '2001-04-19T04:25:21Z',
            $subjectConfirmationData->getNotBefore()->format(C::DATETIME_FORMAT),
        );
        $this->assertEquals(
            '2009-02-13T23:31:30Z',
            $subjectConfirmationData->getNotOnOrAfter()->format(C::DATETIME_FORMAT),
        );
        $this->assertEquals(C::ENTITY_SP, $subjectConfirmationData->getRecipient());
        $this->assertEquals('SomeRequestID', $subjectConfirmationData->getInResponseTo());
        $this->assertEquals('non-IP', $subjectConfirmationData->getAddress());

        $attributes = $subjectConfirmationData->getAttributesNS();
        $this->assertCount(2, $attributes);
        $this->assertEquals('testval1', $attributes[0]->getAttrValue());
        $this->assertEquals('testval2', $attributes[1]->getAttrValue());

        $document = clone self::$xmlRepresentation->documentElement;
        $document->setAttribute('Address', 'non-IP');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML($document),
            strval($subjectConfirmationData),
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshallingEmpty(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmationData xmlns:saml="{$samlNamespace}">
</saml:SubjectConfirmationData>
XML
        );

        $subjectConfirmationData = SubjectConfirmationData::fromXML($document->documentElement);
        $this->assertTrue($subjectConfirmationData->isEmptyElement());
    }
}
