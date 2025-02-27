<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\mdrpi\{AbstractMdrpiElement, Publication, PublicationPath};
use SimpleSAML\SAML2\Type\{SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{ArrayizableElementTestTrait, SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\mdrpi\PublicationPathTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdrpi')]
#[CoversClass(PublicationPath::class)]
#[CoversClass(AbstractMdrpiElement::class)]
final class PublicationPathTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
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
            new Publication(
                SAMLStringValue::fromString('SomePublisher'),
                SAMLDateTimeValue::fromString('2011-01-01T00:00:00Z'),
                SAMLStringValue::fromString('SomePublicationId'),
            ),
            new Publication(
                SAMLStringValue::fromString('SomeOtherPublisher'),
                SAMLDateTimeValue::fromString('2011-01-01T00:00:00Z'),
                SAMLStringValue::fromString('SomeOtherPublicationId'),
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
}
