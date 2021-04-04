<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\PublicationPath;
use SimpleSAML\SAML2\XML\mdrpi\Publication;
use SimpleSAML\Test\XML\ArrayizableXMLTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\mdrpi\PublicationPathTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\PublicationPath
 * @covers \SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement
 * @package simplesamlphp/saml2
 */
final class PublicationPathTest extends TestCase
{
    use ArrayizableXMLTestTrait;
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = PublicationPath::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdrpi_PublicationPath.xml'
        );

        $this->arrayRepresentation = [
            ['publisher' => 'SomePublisher', 'creationInstant' => 1293840000, 'publicationId' => 'SomePublicationId'],
            ['publisher' => 'SomeOtherPublisher', 'creationInstant' => 1293840000, 'publicationId' => 'SomeOtherPublicationId'],
        ];
    }


    /**
     */
    public function testMarshalling(): void
    {
        $publicationPath = new PublicationPath(
            [
                new Publication('SomePublisher', 1293840000, 'SomePublicationId'),
                new Publication('SomeOtherPublisher', 1293840000, 'SomeOtherPublicationId'),
            ]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($publicationPath)
        );
    }


    /**
     * Adding an empty list to an PublicationPath element should yield an empty element. If there were contents already
     * there, those should be left untouched.
     */
    public function testMarshallingWithNoPublications(): void
    {
        $mdrpins = PublicationPath::NS;
        $publicationPath = new PublicationPath([]);
        $this->assertEquals(
            "<mdrpi:PublicationPath xmlns:mdrpi=\"$mdrpins\"/>",
            strval($publicationPath)
        );
        $this->assertTrue($publicationPath->isEmptyElement());
    }

    /**
     */
    public function testUnmarshalling(): void
    {
        $publicationPath = PublicationPath::fromXML($this->xmlRepresentation->documentElement);

        $publication = $publicationPath->getPublication();
        $this->assertCount(2, $publication);

        $this->assertEquals('SomePublisher', $publication[0]->getPublisher());
        $this->assertEquals(1293840000, $publication[0]->getCreationInstant());
        $this->assertEquals('SomePublicationId', $publication[0]->getPublicationId());
        $this->assertEquals('SomeOtherPublisher', $publication[1]->getPublisher());
        $this->assertEquals(1293840000, $publication[1]->getCreationInstant());
        $this->assertEquals('SomeOtherPublicationId', $publication[1]->getPublicationId());
    }
}
