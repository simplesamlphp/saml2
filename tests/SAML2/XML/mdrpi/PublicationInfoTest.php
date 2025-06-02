<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, EntityIDValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\md\{AffiliationDescriptor, EntitiesDescriptor, EntityDescriptor, Extensions};
use SimpleSAML\SAML2\XML\mdrpi\{AbstractMdrpiElement, PublicationInfo, UsagePolicy};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\{ArrayizableElementTestTrait, SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\LanguageValue;

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

    /** @var \DOMDocument */
    private static DOMDocument $affiliationDescriptor;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = PublicationInfo::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdrpi_PublicationInfo.xml',
        );

        self::$affiliationDescriptor = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AffiliationDescriptor.xml',
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
            SAMLStringValue::fromString('SomePublisher'),
            SAMLDateTimeValue::fromString('2011-01-01T00:00:00Z'),
            SAMLStringValue::fromString('SomePublicationId'),
            [
                new UsagePolicy(
                    LanguageValue::fromString('en'),
                    SAMLAnyURIValue::fromString('http://TheEnglishUsagePolicy'),
                ),
                new UsagePolicy(
                    LanguageValue::fromString('no'),
                    SAMLAnyURIValue::fromString('http://TheNorwegianUsagePolicy'),
                ),
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
        PublicationInfo::fromXML($document);
    }


    /**
     */
    public function testMissingPublisherThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<mdrpi:PublicationInfo xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                       creationInstant="2011-01-01T00:00:00Z"
                       publicationId="SomePublicationId">
</mdrpi:PublicationInfo>
XML
            ,
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
        $x = new UsagePolicy(
            LanguageValue::fromString('en'),
            SAMLAnyURIValue::fromString('https://example.org'),
        );
        $x->toXML($document);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdrpi:UsagePolicy>,'
            . ' within a given <mdrpi:PublicationInfo>, for a given language',
        );
        PublicationInfo::fromXML($document);
    }


    /**
     */
    public function testNestedPublicationInfoThrowsException(): void
    {
        $publicationInfo = PublicationInfo::fromXML(self::$xmlRepresentation->documentElement);
        $extensions = new Extensions([$publicationInfo]);

        $ed = new EntityDescriptor(
            entityId: EntityIDValue::fromString('urn:x-simplesamlphp:entity'),
            affiliationDescriptor: AffiliationDescriptor::fromXML(self::$affiliationDescriptor->documentElement),
        );

        $this->expectException(ProtocolViolationException::class);
        new EntitiesDescriptor(
            extensions: $extensions,
            entitiesDescriptors: [
                new EntitiesDescriptor(
                    extensions: $extensions,
                    entityDescriptors: [$ed],
                ),
            ],
        );
    }
}
