<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\UsagePolicy;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\Test\XML\ArrayizableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\mdrpi\PublicationInfoTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\PublicationInfo
 * @covers \SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement
 * @package simplesamlphp/saml2
 */
final class PublicationInfoTest extends TestCase
{
    use ArrayizableXMLTestTrait;
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = PublicationInfo::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdrpi_PublicationInfo.xml'
        );

        $this->arrayRepresentation = [
            'publisher' => 'SomePublisher',
            'creationInstant' => 1293840000,
            'publicationId' => 'SomePublicationId',
            'usagePolicy' => ['en' => 'http://TheEnglishUsagePolicy', 'no' => 'http://TheNorwegianUsagePolicy'],
        ];
    }


    /**
     */
    public function testMarshalling(): void
    {
        $publicationInfo = new PublicationInfo(
            'SomePublisher',
            1293840000,
            'SomePublicationId',
            [
                new UsagePolicy('en', 'http://TheEnglishUsagePolicy'),
                new UsagePolicy('no', 'http://TheNorwegianUsagePolicy'),
            ]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($publicationInfo)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $publicationInfo = PublicationInfo::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('SomePublisher', $publicationInfo->getPublisher());
        $this->assertEquals(1293840000, $publicationInfo->getCreationInstant());
        $this->assertEquals('SomePublicationId', $publicationInfo->getPublicationId());

        $usagePolicy = $publicationInfo->getUsagePolicy();
        $this->assertCount(2, $usagePolicy);
        $this->assertEquals('http://TheEnglishUsagePolicy', $usagePolicy[0]->getValue());
        $this->assertEquals('en', $usagePolicy[0]->getLanguage());
        $this->assertEquals('http://TheNorwegianUsagePolicy', $usagePolicy[1]->getValue());
        $this->assertEquals('no', $usagePolicy[1]->getLanguage());
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
        $document = $this->xmlRepresentation;

        // Append another 'en' UsagePolicy to the document
        $x = new UsagePolicy('en', 'https://example.org');
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdrpi:UsagePolicy>,'
            . ' within a given <mdrpi:PublicationInfo>, for a given language'
        );
        PublicationInfo::fromXML($document->documentElement);
    }
}
