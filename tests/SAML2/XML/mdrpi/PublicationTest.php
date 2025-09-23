<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement;
use SimpleSAML\SAML2\XML\mdrpi\Publication;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\mdrpi\PublicationTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdrpi')]
#[CoversClass(Publication::class)]
#[CoversClass(AbstractMdrpiElement::class)]
final class PublicationTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
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
            SAMLStringValue::fromString('SomePublisher'),
            SAMLDateTimeValue::fromString('2011-01-01T00:00:00Z'),
            SAMLStringValue::fromString('SomePublicationId'),
        );

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
        Publication::fromXML($document);
    }


    /**
     */
    public function testMissingPublisherThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<mdrpi:Publication xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                       creationInstant="2011-01-01T00:00:00Z"
                       publicationId="SomePublicationId">
</mdrpi:Publication>
XML
            ,
        );

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'publisher' attribute on mdrpi:Publication.");
        Publication::fromXML($document->documentElement);
    }
}
