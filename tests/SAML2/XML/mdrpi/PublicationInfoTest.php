<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\UsagePolicy;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\mdrpi\PublicationInfoTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdrpi')]
#[CoversClass(PublicationInfo::class)]
#[CoversClass(AbstractMdrpiElement::class)]
final class PublicationInfoTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-metadata-rpi-v1.0.xsd';

        self::$testedClass = PublicationInfo::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdrpi_PublicationInfo.xml'
        );

        self::$arrayRepresentation = [
            'publisher' => 'SomePublisher',
            'creationInstant' => '2011-01-01T00:00:00Z',
            'publicationId' => 'SomePublicationId',
            'UsagePolicy' => ['en' => 'http://TheEnglishUsagePolicy', 'no' => 'http://TheNorwegianUsagePolicy'],
        ];
    }


    /**
     */
    public function testMarshalling(): void
    {
        $publicationInfo = new PublicationInfo(
            'SomePublisher',
            new DateTimeImmutable('2011-01-01T00:00:00Z'),
            'SomePublicationId',
            [
                new UsagePolicy('en', 'http://TheEnglishUsagePolicy'),
                new UsagePolicy('no', 'http://TheNorwegianUsagePolicy'),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($publicationInfo),
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
        PublicationInfo::fromXML($document);
    }


    /**
     */
    public function testMissingPublisherThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdrpi:PublicationInfo xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                       creationInstant="2011-01-01T00:00:00Z"
                       publicationId="SomePublicationId">
</mdrpi:PublicationInfo>
XML
        );

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'publisher' attribute on mdrpi:PublicationInfo.");
        PublicationInfo::fromXML($document->documentElement);
    }


    /**
     */
    public function testMultipleUsagePoliciesWithSameLanguageThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;

        // Append another 'en' UsagePolicy to the document
        $x = new UsagePolicy('en', 'https://example.org');
        $x->toXML($document);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdrpi:UsagePolicy>,'
            . ' within a given <mdrpi:PublicationInfo>, for a given language'
        );
        PublicationInfo::fromXML($document);
    }
}
