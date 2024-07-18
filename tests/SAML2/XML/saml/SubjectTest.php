<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\AbstractBaseID;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\CustomBaseID;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\SubjectTest
 *
 * @package SimpleSAMLphp
 */
#[Group('saml')]
#[CoversClass(Subject::class)]
#[CoversClass(AbstractSamlElement::class)]
final class SubjectTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \DOMDocument */
    private static DOMDocument $subject;

    /** @var \DOMDocument */
    private static DOMDocument $baseId;

    /** @var \DOMDocument */
    private static DOMDocument $nameId;

    /** @var \DOMDocument */
    private static DOMDocument $subjectConfirmation;


    public function setup(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        self::$testedClass = Subject::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Subject.xml',
        );

        self::$subject = DOMDocumentFactory::fromString(
            <<<XML
<saml:Subject xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"></saml:Subject>
XML
            ,
        );

        self::$baseId = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_BaseID.xml',
        );

        self::$nameId = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_NameID.xml',
        );

        self::$subjectConfirmation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_SubjectConfirmation.xml',
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
            [$attr1, $attr2],
        );

        $subject = new Subject(
            new NameID(
                value: 'SomeNameIDValue',
                SPNameQualifier: 'https://sp.example.org/authentication/sp/metadata',
                Format: C::NAMEID_TRANSIENT,
            ),
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    new NameID(
                        value: 'SomeOtherNameIDValue',
                        SPNameQualifier: 'https://sp.example.org/authentication/sp/metadata',
                        Format: C::NAMEID_TRANSIENT,
                    ),
                    $subjectConfirmationData,
                ),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($subject),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
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
            [$attr1, $attr2],
        );

        $subject = new Subject(
            new NameID(
                value: 'SomeNameIDValue',
                SPNameQualifier: 'https://sp.example.org/authentication/sp/metadata',
                Format: C::NAMEID_TRANSIENT,
            ),
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    new NameID(
                        value: 'SomeOtherNameIDValue',
                        SPNameQualifier: 'https://sp.example.org/authentication/sp/metadata',
                        Format: C::NAMEID_TRANSIENT,
                    ),
                    $subjectConfirmationData,
                ),
            ],
        );

        // Marshall it to a \DOMElement
        $subjectElement = $subject->toXML();

        // Test for a NameID
        $xpCache = XPath::getXPath($subjectElement);
        $subjectElements = XPath::xpQuery($subjectElement, './saml_assertion:NameID', $xpCache);
        $this->assertCount(1, $subjectElements);

        // Test ordering of Subject contents
        /** @psalm-var \DOMElement[] $subjectElements */
        $subjectElements = XPath::xpQuery($subjectElement, './saml_assertion:NameID/following-sibling::*', $xpCache);
        $this->assertCount(1, $subjectElements);
        $this->assertEquals('saml:SubjectConfirmation', $subjectElements[0]->tagName);
    }


    /**
     */
    public function testMarshallingBaseID(): void
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
            [$attr1, $attr2],
        );

        $subject = new Subject(
            new CustomBaseID(
                [new Audience('urn:some:audience')],
                'TheNameQualifier',
                'TheSPNameQualifier',
            ),
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    new NameID(
                        value: 'SomeNameIDValue',
                        SPNameQualifier: 'https://sp.example.org/authentication/sp/metadata',
                        Format: C::NAMEID_TRANSIENT,
                    ),
                    $subjectConfirmationData,
                ),
            ],
        );

        $this->assertNotNull($subject->getIdentifier());
        $this->assertInstanceOf(CustomBaseID::class, $subject->getIdentifier());

        $subjectConfirmation = $subject->getSubjectConfirmation();
        $this->assertNotEmpty($subjectConfirmation);

        $document = clone self::$subject;
        AbstractBaseID::fromXML(self::$baseId->documentElement)->toXML($document->documentElement);
        SubjectConfirmation::fromXML(self::$subjectConfirmation->documentElement)->toXML($document->documentElement);

        $this->assertXmlStringEqualsXmlString($document->saveXML(), strval($subject));
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $document = clone self::$subject;

        $nameId = clone self::$nameId;
        $document->documentElement->appendChild($document->importNode($nameId->documentElement, true));

        $subject = Subject::fromXML($document->documentElement);

        $this->assertNotNull($subject->getIdentifier());
        $this->assertInstanceOf(NameID::class, $subject->getIdentifier());

        $this->assertXmlStringEqualsXmlString($document->saveXML(), strval($subject));
    }


    /**
     */
    public function testEmptySubjectThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString('<saml:Subject xmlns:saml="' . Subject::NS . '"/>');

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage(
            'A <saml:Subject> not containing <saml:SubjectConfirmation> should provide '
            . 'exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>',
        );

        Subject::fromXML($document->documentElement);
    }


    /**
     */
    public function testManyNameIDThrowsException(): void
    {
        $document = clone self::$subject;
        $nameId = clone self::$nameId;
        $document->documentElement->appendChild($document->importNode($nameId->documentElement, true));

        $nameId->documentElement->textContent = 'AnotherNameIDValue';
        $document->documentElement->appendChild($document->importNode($nameId->documentElement, true));

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage('More than one <saml:NameID> in <saml:Subject>.');

        Subject::fromXML($document->documentElement);
    }


    /**
     * Test that unmarshalling a Subject from an XML with multiple identifiers fails.
     */
    public function testMultipleIdentifiers(): void
    {
        $samlNamespace = Subject::NS;
        $xsiNamespace = C::NS_XSI;

        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:Subject xmlns:saml="{$samlNamespace}">
  <saml:BaseID xmlns:xsi="{$xsiNamespace}" xsi:type="CustomBaseIDType">
    <saml:Audience>urn:some:audience</saml:Audience>
  </saml:BaseID>
  <saml:NameID
      SPNameQualifier="https://sp.example.org/authentication/sp/metadata"
      Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">SomeOtherNameIDValue</saml:NameID>
  <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
    <saml:SubjectConfirmationData
        NotOnOrAfter="2020-02-27T11:26:36Z"
        Recipient="https://sp.example.org/authentication/sp/consume-assertion"
        InResponseTo="def456"/>
  </saml:SubjectConfirmation>
</saml:Subject>
XML
            ,
        );

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage(
            'A <saml:Subject> can contain exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>.',
        );
        Subject::fromXML($document->documentElement);
    }
}
