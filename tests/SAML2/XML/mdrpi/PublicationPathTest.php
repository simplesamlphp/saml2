<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\PublicationPath;
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
 * Class \SAML2\XML\mdrpi\PublicationPathTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\PublicationPath
 * @covers \SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement
 *
 * @package simplesamlphp/saml2
 */
final class PublicationPathTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/schemas/saml-metadata-rpi-v1.0.xsd';

        $this->testedClass = PublicationPath::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdrpi_PublicationPath.xml',
        );

        $this->arrayRepresentation = [
            [
                'publisher' => 'SomePublisher',
                'creationInstant' => 1293840000,
                'publicationId' => 'SomePublicationId',
            ],
            [
                'publisher' => 'SomeOtherPublisher',
                'creationInstant' => 1293840000,
                'publicationId' => 'SomeOtherPublicationId',
            ],
        ];
    }


    /**
     */
    public function testMarshalling(): void
    {
        $publicationPath = new PublicationPath([
            new Publication('SomePublisher', 1293840000, 'SomePublicationId'),
            new Publication('SomeOtherPublisher', 1293840000, 'SomeOtherPublicationId'),
        ]);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($publicationPath),
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
            strval($publicationPath),
        );
        $this->assertTrue($publicationPath->isEmptyElement());
    }

    /**
     */
    public function testUnmarshalling(): void
    {
        $publicationPath = PublicationPath::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($publicationPath),
        );
    }
}
