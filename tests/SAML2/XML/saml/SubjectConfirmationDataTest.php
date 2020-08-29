<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\XML\Chunk;
use SimpleSAML\SAML2\XML\ds\KeyInfo;
use SimpleSAML\SAML2\XML\ds\KeyName;

/**
 * Class \SAML2\XML\saml\SubjectConfirmationDataTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationDataTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_SubjectConfirmationData.xml'
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $arbitrary = DOMDocumentFactory::fromString('<some>Arbitrary Element</some>');

        $attr1 = $this->document->createAttributeNS('urn:test', 'test:attr1');
        $attr1->value = 'testval1';
        $attr2 = $this->document->createAttributeNS('urn:test', 'test:attr2');
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

        $this->assertEquals(987654321, $subjectConfirmationData->getNotBefore());
        $this->assertEquals(1234567890, $subjectConfirmationData->getNotOnOrAfter());
        $this->assertEquals('https://sp.example.org/asdf', $subjectConfirmationData->getRecipient());
        $this->assertEquals('SomeRequestID', $subjectConfirmationData->getInResponseTo());
        $this->assertEquals('127.0.0.1', $subjectConfirmationData->getAddress());

        $this->assertEquals('testval1', $subjectConfirmationData->getAttributeNS('urn:test', 'attr1'));
        $this->assertEquals('testval2', $subjectConfirmationData->getAttributeNS('urn:test', 'attr2'));

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($subjectConfirmationData)
        );
    }


    /**
     * @return void
     */
    public function testMarshallingWithNonIPAddress(): void
    {
        $arbitrary = DOMDocumentFactory::fromString('<some>Arbitrary Element</some>');

        $attr1 = $this->document->createAttributeNS('urn:test', 'test:attr1');
        $attr1->value = 'testval1';
        $attr2 = $this->document->createAttributeNS('urn:test', 'test:attr2');
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

        $document = $this->document->documentElement;
        $document->setAttribute('Address', 'non-IP');

        $this->assertEquals(
            $this->document->saveXML($document),
            strval($subjectConfirmationData)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $subjectConfirmationData = SubjectConfirmationData::fromXML($this->document->documentElement);
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

        /** @psalm-var \SimpleSAML\SAML2\XML\ds\KeyInfo $info */
        $info = $subjectConfirmationData->getInfo()[0];

        /** @psalm-var \SimpleSAML\SAML2\XML\ds\KeyName $keyName */
        $keyName = $info->getInfo()[0];

        /** @psalm-var \SimpleSAML\SAML2\XML\Chunk $info */
        $info = $subjectConfirmationData->getInfo()[1];

        $this->assertEquals('SomeKey', $keyName->getName());
    }


    /**
     * @return void
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


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(SubjectConfirmationData::fromXML($this->document->documentElement))))
        );
    }
}
