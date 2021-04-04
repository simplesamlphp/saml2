<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\Publication;
use SimpleSAML\Test\XML\ArrayizableXMLTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\mdrpi\PublicationTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\Publication
 * @covers \SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement
 * @package simplesamlphp/saml2
 */
final class PublicationTest extends TestCase
{
    use ArrayizableXMLTestTrait;
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = Publication::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdrpi_Publication.xml'
        );

        $this->arrayRepresentation = ['publisher' => 'SomePublisher', 'creationInstant' => 1234567890, 'publicationId' => 'SomePublicationId'];
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

        $this->assertEquals('SomePublisher', $publication->getPublisher());
        $this->assertEquals(1293840000, $publication->getCreationInstant());
        $this->assertEquals('SomePublicationId', $publication->getPublicationId());
    }


    /**
     */
    public function testCreationInstantTimezoneNotZuluThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->setAttribute('creationInstant', '2011-01-01T00:00:00WT');

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            "Time values MUST be expressed in the UTC timezone using the 'Z' timezone identifier."
        );
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
