<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\Publication;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\Utils as XMLUtils;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\mdrpi\PublicationTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\Publication
 * @covers \SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement
 *
 * @package simplesamlphp/saml2
 */
final class PublicationTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-metadata-rpi-v1.0.xsd';

        self::$testedClass = Publication::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdrpi_Publication.xml',
        );

        self::$arrayRepresentation = [
            'publisher' => 'SomePublisher',
            'creationInstant' => '2011-01-01T00:00:00Z',
            'publicationId' => 'SomePublicationId',
        ];
    }


    /**
     */
    public function testMarshalling(): void
    {
        $publication = new Publication(
            'SomePublisher',
            new DateTimeImmutable('2011-01-01T00:00:00Z'),
            'SomePublicationId',
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($publication),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $publication = Publication::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($publication),
        );
    }


    /**
     */
    public function testCreationInstantTimezoneNotZuluThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->setAttribute('creationInstant', '2011-01-01T00:00:00WT');

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage("'2011-01-01T00:00:00WT' is not a valid DateTime");
        Publication::fromXML($document);
    }


    /**
     */
    public function testMissingPublisherThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdrpi:Publication xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                       creationInstant="2011-01-01T00:00:00Z"
                       publicationId="SomePublicationId">
</mdrpi:Publication>
XML
        );

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'publisher' attribute on mdrpi:Publication.");
        Publication::fromXML($document->documentElement);
    }
}
