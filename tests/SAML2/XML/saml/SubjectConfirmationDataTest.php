<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\{SAMLDateTimeValue, EntityIDValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\saml\{AbstractSamlElement, SubjectConfirmationData};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\{Attribute as XMLAttribute, Chunk, DOMDocumentFactory};
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XMLSchema\Type\{NCNameValue, StringValue};
use SimpleSAML\XMLSecurity\XML\ds\{KeyInfo, KeyName};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\SubjectConfirmationDataTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(SubjectConfirmationData::class)]
#[CoversClass(AbstractSamlElement::class)]
final class SubjectConfirmationDataTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
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

        $attr1 = new XMLAttribute('urn:test:something', 'test', 'attr1', StringValue::fromString('testval1'));
        $attr2 = new XMLAttribute('urn:test:something', 'test', 'attr2', StringValue::fromString('testval2'));

        $subjectConfirmationData = new SubjectConfirmationData(
            SAMLDateTimeValue::fromString('2001-04-19T04:25:21Z'),
            SAMLDateTimeValue::fromString('2009-02-13T23:31:30Z'),
            EntityIDValue::fromString(C::ENTITY_SP),
            NCNameValue::fromString('SomeRequestID'),
            SAMLStringValue::fromString('127.0.0.1'),
            [
                new KeyInfo([
                    new KeyName(
                        StringValue::fromString('SomeKey'),
                    ),
                ]),
                new Chunk($arbitrary->documentElement),
            ],
            [$attr1, $attr2],
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

        $attr1 = new XMLAttribute('urn:test:something', 'test', 'attr1', StringValue::fromString('testval1'));
        $attr2 = new XMLAttribute('urn:test:something', 'test', 'attr2', StringValue::fromString('testval2'));

        $subjectConfirmationData = new SubjectConfirmationData(
            SAMLDateTimeValue::fromString('2001-04-19T04:25:21Z'),
            SAMLDateTimeValue::fromString('2009-02-13T23:31:30Z'),
            EntityIDValue::fromString(C::ENTITY_SP),
            NCNameValue::fromString('SomeRequestID'),
            SAMLStringValue::fromString('non-IP'),
            [
                new KeyInfo([
                    new KeyName(
                        StringValue::fromString('SomeKey'),
                    ),
                ]),
                new Chunk($arbitrary->documentElement),
            ],
            [$attr1, $attr2],
        );

        $this->assertEquals(
            '2001-04-19T04:25:21Z',
            $subjectConfirmationData->getNotBefore()->getValue(),
        );
        $this->assertEquals(
            '2009-02-13T23:31:30Z',
            $subjectConfirmationData->getNotOnOrAfter()->getValue(),
        );
        $this->assertEquals(C::ENTITY_SP, $subjectConfirmationData->getRecipient()->getValue());
        $this->assertEquals('SomeRequestID', $subjectConfirmationData->getInResponseTo()->getValue());
        $this->assertEquals('non-IP', $subjectConfirmationData->getAddress()->getValue());

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
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:SubjectConfirmationData xmlns:saml="{$samlNamespace}">
</saml:SubjectConfirmationData>
XML
            ,
        );

        $subjectConfirmationData = SubjectConfirmationData::fromXML($document->documentElement);
        $this->assertTrue($subjectConfirmationData->isEmptyElement());
    }
}
