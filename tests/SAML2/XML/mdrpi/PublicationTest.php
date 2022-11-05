<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\Publication;
use SimpleSAML\Test\XML\ArrayizableElementTestTrait;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
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
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-metadata-rpi-v1.0.xsd';

        $this->testedClass = Publication::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdrpi_Publication.xml'
        );

        $this->arrayRepresentation = [
            'publisher' => 'SomePublisher',
            'creationInstant' => 1234567890,
            'publicationId' => 'SomePublicationId'
        ];
    }


    /**
     */
    public function testMarshalling(): void
    {
        $publication = new Publication(
            'SomePublisher',
            1293840000,
            'SomePublicationId'
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($publication)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $publication = Publication::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($publication)
        );
    }


    /**
     */
    public function testCreationInstantTimezoneNotZuluThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
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
