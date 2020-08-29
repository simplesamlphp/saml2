<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\mdrpi;

use PHPUnit\Framework\TestCase;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\mdrpi\PublicationInfoTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\PublicationInfo
 * @package simplesamlphp/saml2
 */
final class PublicationInfoTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdrpi_PublicationInfo.xml'
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $publicationInfo = new PublicationInfo(
            'TestPublisher',
            1234567890,
            'PublicationIdValue',
            [
                'en' => 'http://EnglishUsagePolicy',
                'no' => 'http://NorwegianUsagePolicy',
            ]
        );

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $publicationInfo->toXML($document->documentElement);

        /** @var \DOMElement[] $publicationInfoElements */
        $publicationInfoElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'PublicationInfo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:rpi\']'
        );
        $this->assertCount(1, $publicationInfoElements);
        $publicationInfoElement = $publicationInfoElements[0];

        $this->assertEquals('TestPublisher', $publicationInfoElement->getAttribute("publisher"));
        $this->assertEquals('2009-02-13T23:31:30Z', $publicationInfoElement->getAttribute("creationInstant"));
        $this->assertEquals('PublicationIdValue', $publicationInfoElement->getAttribute("publicationId"));

        /** @var \DOMElement[] $usagePolicyElements */
        $usagePolicyElements = Utils::xpQuery(
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
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $publicationInfo = PublicationInfo::fromXML($this->document->documentElement);

        $this->assertEquals('SomePublisher', $publicationInfo->getPublisher());
        $this->assertEquals(1293840000, $publicationInfo->getCreationInstant());
        $this->assertEquals('SomePublicationId', $publicationInfo->getPublicationId());

        $usagePolicy = $publicationInfo->getUsagePolicy();
        $this->assertCount(2, $usagePolicy);
        $this->assertEquals('http://TheEnglishUsagePolicy', $usagePolicy["en"]);
        $this->assertEquals('http://TheNorwegianUsagePolicy', $usagePolicy["no"]);
    }


    /**
     * @return void
     */
    public function testMissingPublisherThrowsException()
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
        $publicationInfo = PublicationInfo::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(PublicationInfo::fromXML($this->document->documentElement))))
        );
    }
}
