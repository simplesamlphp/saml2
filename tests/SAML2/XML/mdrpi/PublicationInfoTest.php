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
            'publisher' => 'TestPublisher',
            'creationInstant' => 12345678990,
            'publicationId' => 'PublicationIdValue',
            'usagePolicy' => ['en' => 'http://EnglishUsagePolicy', 'no' => 'http://NorwegianUsagePolicy'],
        ];
    }


    /**
     */
    public function testMarshalling(): void
    {
        $publicationInfo = new PublicationInfo(
            'TestPublisher',
            1234567890,
            'PublicationIdValue',
            [
                new UsagePolicy('en', 'http://EnglishUsagePolicy'),
                new UsagePolicy('no', 'http://NorwegianUsagePolicy'),
            ]
        );

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $publicationInfo->toXML($document->documentElement);

        /** @var \DOMElement[] $publicationInfoElements */
        $publicationInfoElements = XMLUtils::xpQuery(
            $xml,
            '/root/*[local-name()=\'PublicationInfo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:rpi\']'
        );
        $this->assertCount(1, $publicationInfoElements);
        $publicationInfoElement = $publicationInfoElements[0];

        $this->assertEquals('TestPublisher', $publicationInfoElement->getAttribute("publisher"));
        $this->assertEquals('2009-02-13T23:31:30Z', $publicationInfoElement->getAttribute("creationInstant"));
        $this->assertEquals('PublicationIdValue', $publicationInfoElement->getAttribute("publicationId"));

        /** @var \DOMElement[] $usagePolicyElements */
        $usagePolicyElements = XMLUtils::xpQuery(
            $publicationInfoElement,
            './*[local-name()=\'UsagePolicy\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:rpi\']'
        );
        $this->assertCount(2, $usagePolicyElements);

        $this->assertEquals(
            'en',
            $usagePolicyElements[0]->getAttributeNS("http://www.w3.org/XML/1998/namespace", "lang")
        );
        $this->assertEquals('http://EnglishUsagePolicy', $usagePolicyElements[0]->textContent);
        $this->assertEquals(
            'no',
            $usagePolicyElements[1]->getAttributeNS("http://www.w3.org/XML/1998/namespace", "lang")
        );
        $this->assertEquals('http://NorwegianUsagePolicy', $usagePolicyElements[1]->textContent);
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
