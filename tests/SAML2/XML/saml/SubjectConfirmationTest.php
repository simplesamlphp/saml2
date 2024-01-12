<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\SubjectConfirmationTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\SubjectConfirmation
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;


    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();

        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        self::$testedClass = SubjectConfirmation::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_SubjectConfirmation.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $attr1 = new XMLAttribute('urn:test:something', 'test', 'attr1', 'testval1');
        $attr2 = new XMLAttribute('urn:test:something', 'test', 'attr2', 'testval2');

        $subjectConfirmation = new SubjectConfirmation(
            C::CM_BEARER,
            new NameID(
                'SomeNameIDValue',
                null,
                'https://sp.example.org/authentication/sp/metadata',
                C::NAMEID_TRANSIENT,
            ),
            new SubjectConfirmationData(
                new DateTimeImmutable('2001-04-19T04:25:21Z'),
                new DateTimeImmutable('2009-02-13T23:31:30Z'),
                C::ENTITY_SP,
                'SomeRequestID',
                '127.0.0.1',
                [
                    new KeyInfo([new KeyName('SomeKey')]),
                    new Chunk(DOMDocumentFactory::fromString('<some>Arbitrary Element</some>')->documentElement),
                ],
                [$attr1, $attr2],
            ),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($subjectConfirmation),
        );
    }


    /**
     */
    public function testMarshallingEmptySubjectConfirmationData(): void
    {
        $subjectConfirmation = new SubjectConfirmation(
            C::CM_BEARER,
            new NameID('SomeNameIDValue'),
            new SubjectConfirmationData(),
        );
        $ns_saml = C::NS_SAML;

        $doc = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$ns_saml}" Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
  <saml:NameID>SomeNameIDValue</saml:NameID>
</saml:SubjectConfirmation>
XML
        );

        $this->assertEquals(
            $doc->saveXML($doc->documentElement),
            strval($subjectConfirmation),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $subjectConfirmation = new SubjectConfirmation(
            C::CM_BEARER,
            new NameID('SomeNameIDValue'),
            new SubjectConfirmationData(self::$clock->now()),
        );

        // Marshall it to a \DOMElement
        $subjectConfirmationElement = $subjectConfirmation->toXML();

        // Test for a NameID
        $xpCache = XPath::getXPath($subjectConfirmationElement);
        $subjectConfirmationElements = XPath::xpQuery($subjectConfirmationElement, './saml_assertion:NameID', $xpCache);
        $this->assertCount(1, $subjectConfirmationElements);

        // Test ordering of SubjectConfirmation contents
        /** @psalm-var \DOMElement[] $subjectConfirmationElements */
        $subjectConfirmationElements = XPath::xpQuery(
            $subjectConfirmationElement,
            './saml_assertion:NameID/following-sibling::*',
            $xpCache,
        );
        $this->assertCount(1, $subjectConfirmationElements);
        $this->assertEquals('saml:SubjectConfirmationData', $subjectConfirmationElements[0]->tagName);
    }


    // unmarshalling


    /**
     */
    public function testMethodMissingThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttribute('Method');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'Method' attribute on saml:SubjectConfirmation.");

        SubjectConfirmation::fromXML($document);
    }


    /**
     */
    public function testManyNameIDThrowsException(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:NameID>AnotherNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
        );

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage('More than one <saml:NameID> in <saml:SubjectConfirmation>.');
        SubjectConfirmation::fromXML($document->documentElement);
    }


    /**
     * Test that creating a SubjectConfirmation fails with multiple identifiers of different types.
     */
    public function testMultipleIdentifiers(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
  <saml:BaseID xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="someType">SomeNameIDValue</saml:BaseID>
  <saml:NameID>AnotherNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
        );

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage(
            'A <saml:SubjectConfirmation> can contain exactly one '
            . 'of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>.'
        );
        SubjectConfirmation::fromXML($document->documentElement);
    }


    /**
     */
    public function testManySubjectConfirmationDataThrowsException(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData Recipient="Me" />
  <saml:SubjectConfirmationData Recipient="Someone Else" />
</saml:SubjectConfirmation>
XML
        );

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage(
            'More than one <saml:SubjectConfirmationData> in <saml:SubjectConfirmation>.',
        );
        SubjectConfirmation::fromXML($document->documentElement);
    }
}
