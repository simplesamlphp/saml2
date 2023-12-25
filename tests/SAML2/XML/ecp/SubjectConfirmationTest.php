<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\ecp;

use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\ecp\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\SOAP\Constants as SOAP;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * @covers \SimpleSAML\SAML2\XML\ecp\AbstractEcpElement
 * @covers \SimpleSAML\SAML2\XML\ecp\SubjectConfirmation
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmationTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-ecp-2.0.xsd';

        self::$testedClass = SubjectConfirmation::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/ecp_SubjectConfirmation.xml'
        );
    }


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

        $subjectConfirmation = new SubjectConfirmation(C::CM_BEARER, $subjectConfirmationData);

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
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV_11, 'mustUnderstand');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:mustUnderstand attribute in <ecp:SubjectConfirmation>.');

        SubjectConfirmation::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV_11, 'actor');

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
