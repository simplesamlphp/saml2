<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\PublicationPath;
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
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-metadata-rpi-v1.0.xsd';

        self::$testedClass = PublicationPath::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdrpi_PublicationPath.xml',
        );

        self::$arrayRepresentation = [
            [
                'publisher' => 'SomePublisher',
                'creationInstant' => '2011-01-01T00:00:00Z',
                'publicationId' => 'SomePublicationId',
            ],
            [
                'publisher' => 'SomeOtherPublisher',
                'creationInstant' => '2011-01-01T00:00:00Z',
                'publicationId' => 'SomeOtherPublicationId',
            ],
        ];
    }


    /**
     */
    public function testMarshalling(): void
    {
        $publicationPath = new PublicationPath([
            new Publication('SomePublisher', new DateTimeImmutable('2011-01-01T00:00:00Z'), 'SomePublicationId'),
            new Publication(
                'SomeOtherPublisher',
                new DateTimeImmutable('2011-01-01T00:00:00Z'),
                'SomeOtherPublicationId',
            ),
        ]);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
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
        $publicationPath = PublicationPath::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($publicationPath),
        );
    }
}
