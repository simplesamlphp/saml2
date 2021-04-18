<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;

/**
 * Class \SAML2\XML\saml\SubjectConfirmationDataTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationDataTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setup(): void
    {
        $this->testedClass = SubjectConfirmationData::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_SubjectConfirmationData.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $arbitrary = DOMDocumentFactory::fromString('<some>Arbitrary Element</some>');

        $attr1 = $this->xmlRepresentation->createAttributeNS('urn:test', 'test:attr1');
        $attr1->value = 'testval1';
        $attr2 = $this->xmlRepresentation->createAttributeNS('urn:test', 'test:attr2');
        $attr2->value = 'testval2';

        $subjectConfirmationData = new SubjectConfirmationData(
            987654321,
            1234567890,
            'https://sp.example.org/asdf',
            'SomeRequestID',
            '127.0.0.1',
            [
                new KeyInfo([new KeyName('SomeKey')]),
                new Chunk($arbitrary->documentElement),
            ],
            [$attr1, $attr2]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($subjectConfirmationData)
        );
    }


    /**
     */
    public function testMarshallingWithNonIPAddress(): void
    {
        $arbitrary = DOMDocumentFactory::fromString('<some>Arbitrary Element</some>');

        $attr1 = $this->xmlRepresentation->createAttributeNS('urn:test', 'test:attr1');
        $attr1->value = 'testval1';
        $attr2 = $this->xmlRepresentation->createAttributeNS('urn:test', 'test:attr2');
        $attr2->value = 'testval2';

        $subjectConfirmationData = new SubjectConfirmationData(
            987654321,
            1234567890,
            'https://sp.example.org/asdf',
            'SomeRequestID',
            'non-IP',
            [
                new KeyInfo([new KeyName('SomeKey')]),
                new Chunk($arbitrary->documentElement),
            ],
            [$attr1, $attr2]
        );

        $this->assertEquals(987654321, $subjectConfirmationData->getNotBefore());
        $this->assertEquals(1234567890, $subjectConfirmationData->getNotOnOrAfter());
        $this->assertEquals('https://sp.example.org/asdf', $subjectConfirmationData->getRecipient());
        $this->assertEquals('SomeRequestID', $subjectConfirmationData->getInResponseTo());
        $this->assertEquals('non-IP', $subjectConfirmationData->getAddress());

        $this->assertEquals('testval1', $subjectConfirmationData->getAttributeNS('urn:test', 'attr1'));
        $this->assertEquals('testval2', $subjectConfirmationData->getAttributeNS('urn:test', 'attr2'));

        $document = $this->xmlRepresentation->documentElement;
        $document->setAttribute('Address', 'non-IP');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($document),
            strval($subjectConfirmationData)
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $subjectConfirmationData = SubjectConfirmationData::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(987654321, $subjectConfirmationData->getNotBefore());
        $this->assertEquals(1234567890, $subjectConfirmationData->getNotOnOrAfter());
        $this->assertEquals('https://sp.example.org/asdf', $subjectConfirmationData->getRecipient());
        $this->assertEquals('SomeRequestID', $subjectConfirmationData->getInResponseTo());
        $this->assertEquals('127.0.0.1', $subjectConfirmationData->getAddress());

        $this->assertEquals(
            [
                '{urn:test}attr1' => [
                    'qualifiedName' => 'test:attr1',
                    'namespaceURI' => 'urn:test',
                    'value' => 'testval1'
                ],
                '{urn:test}attr2' => [
                    'qualifiedName' => 'test:attr2',
                    'namespaceURI' => 'urn:test',
                    'value' => 'testval2'
                ]
            ],
            $subjectConfirmationData->getAttributesNS()
        );
        $this->assertEquals('testval1', $subjectConfirmationData->getAttributeNS('urn:test', 'attr1'));
        $this->assertEquals('testval2', $subjectConfirmationData->getAttributeNS('urn:test', 'attr2'));

        /** @psalm-var \SimpleSAML\XMLSecurity\XML\ds\KeyInfo $info */
        $info = $subjectConfirmationData->getInfo()[0];

        /** @psalm-var \SimpleSAML\XMLSecurity\XML\ds\KeyName $keyName */
        $keyName = $info->getInfo()[0];
        $this->assertEquals('SomeKey', $keyName->getContent());

        $info = $subjectConfirmationData->getInfo()[1];
        $this->assertInstanceOf(Chunk::class, $info);
    }


    /**
     */
    public function testUnmarshallingEmpty(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmationData xmlns:saml="{$samlNamespace}">
</saml:SubjectConfirmationData>
XML
        );

        $subjectConfirmationData = SubjectConfirmationData::fromXML($document->documentElement);
        $this->assertTrue($subjectConfirmationData->isEmptyElement());
    }
}
