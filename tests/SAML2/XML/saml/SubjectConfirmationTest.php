<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\NCNameValue;
use SimpleSAML\XMLSchema\Type\StringValue;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\SubjectConfirmationTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(SubjectConfirmation::class)]
#[CoversClass(AbstractSamlElement::class)]
final class SubjectConfirmationTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;


    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();

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
        $attr1 = new XMLAttribute('urn:test:something', 'test', 'attr1', StringValue::fromString('testval1'));
        $attr2 = new XMLAttribute('urn:test:something', 'test', 'attr2', StringValue::fromString('testval2'));

        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_BEARER),
            new NameID(
                SAMLStringValue::fromString('SomeNameIDValue'),
                null,
                SAMLStringValue::fromString('https://sp.example.org/authentication/sp/metadata'),
                SAMLAnyURIValue::fromString(C::NAMEID_TRANSIENT),
            ),
            new SubjectConfirmationData(
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
            SAMLAnyURIValue::fromString(C::CM_BEARER),
            new NameID(
                SAMLStringValue::fromString('SomeNameIDValue'),
            ),
            new SubjectConfirmationData(),
        );
        $ns_saml = C::NS_SAML;

        $doc = DOMDocumentFactory::fromString(
            <<<XML
<saml:SubjectConfirmation xmlns:saml="{$ns_saml}" Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
  <saml:NameID>SomeNameIDValue</saml:NameID>
</saml:SubjectConfirmation>
XML
            ,
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
            SAMLAnyURIValue::fromString(C::CM_BEARER),
            new NameID(
                SAMLStringValue::fromString('SomeNameIDValue'),
            ),
            new SubjectConfirmationData(
                SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            ),
        );

        // Marshall it to a \DOMElement
        $subjectConfirmationElement = $subjectConfirmation->toXML();

        // Test for a NameID
        $xpCache = XPath::getXPath($subjectConfirmationElement);
        $subjectConfirmationElements = XPath::xpQuery($subjectConfirmationElement, './saml_assertion:NameID', $xpCache);
        $this->assertCount(1, $subjectConfirmationElements);

        // Test ordering of SubjectConfirmation contents
        /** @var \DOMElement[] $subjectConfirmationElements */
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
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:NameID>AnotherNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
            ,
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
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
  <saml:BaseID xmlns:ssp="urn:x-simplesamlphp:namespace" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="ssp:someType">SomeNameIDValue</saml:BaseID>
  <saml:NameID>AnotherNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
            ,
        );

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage(
            'A <saml:SubjectConfirmation> can contain exactly one '
            . 'of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>.',
        );
        SubjectConfirmation::fromXML($document->documentElement);
    }


    /**
     */
    public function testManySubjectConfirmationDataThrowsException(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData Recipient="https://simplesamlphp.org/sp/metadata" />
  <saml:SubjectConfirmationData Recipient="https://example.org/metadata" />
</saml:SubjectConfirmation>
XML
            ,
        );

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage(
            'More than one <saml:SubjectConfirmationData> in <saml:SubjectConfirmation>.',
        );
        SubjectConfirmation::fromXML($document->documentElement);
    }
}
