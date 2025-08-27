<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\ecp;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, EntityIDValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\ecp\{AbstractEcpElement, SubjectConfirmation};
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\SOAP11\Constants as SOAP;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\{Chunk, DOMDocumentFactory};
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;
use SimpleSAML\XMLSchema\Type\{IDValue, NCNameValue, StringValue};
use SimpleSAML\XMLSecurity\XML\ds\{KeyInfo, KeyName};

use function dirname;
use function strval;

/**
 * @package simplesamlphp/saml2
 */
#[Group('ecp')]
#[CoversClass(SubjectConfirmation::class)]
#[CoversClass(AbstractEcpElement::class)]
final class SubjectConfirmationTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = SubjectConfirmation::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/ecp_SubjectConfirmation.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $arbitrary = DOMDocumentFactory::fromString('<some>Arbitrary Element</some>');

        $attr1 = new XMLAttribute(
            'urn:test:something',
            'test',
            'attr1',
            SAMLStringValue::fromString('testval1'),
        );
        $attr2 = new XMLAttribute(
            'urn:test:something',
            'test',
            'attr2',
            SAMLStringValue::fromString('testval2'),
        );

        $subjectConfirmationData = new SubjectConfirmationData(
            SAMLDateTimeValue::fromString('2001-04-19T04:25:21Z'),
            SAMLDateTimeValue::fromString('2009-02-13T23:31:30Z'),
            EntityIDValue::fromString(C::ENTITY_SP),
            NCNameValue::fromString('SomeRequestID'),
            SAMLStringValue::fromString('127.0.0.1'),
            [
                new KeyInfo([
                    new KeyName(StringValue::fromString('SomeKey')),
                ]),
                new Chunk($arbitrary->documentElement),
            ],
            [$attr1, $attr2],
        );

        $subjectConfirmation = new SubjectConfirmation(
            SAMLAnyURIValue::fromString(C::CM_BEARER),
            $subjectConfirmationData,
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($subjectConfirmation),
        );
    }


    /**
     */
    public function testUnmarshallingWithMissingMustUnderstandThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV, 'mustUnderstand');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:mustUnderstand attribute in <ecp:SubjectConfirmation>.');

        SubjectConfirmation::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV, 'actor');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:actor attribute in <ecp:SubjectConfirmation>.');

        SubjectConfirmation::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingMethodThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttribute('Method');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'Method\' attribute on ecp:SubjectConfirmation.');

        SubjectConfirmation::fromXML($document);
    }
}
