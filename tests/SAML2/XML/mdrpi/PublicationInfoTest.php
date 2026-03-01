<?php

declare(strict_types=1);

namespace SAML2\XML\mdrpi;

use Exception;
use SAML2\XML\mdrpi\PublicationInfo;
use SAML2\Utils;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\mdrpi\PublicationInfoTest
 */
class PublicationInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $publicationInfo = new PublicationInfo();
        $publicationInfo->setPublisher('TestPublisher');
        $publicationInfo->setCreationInstant(1234567890);
        $publicationInfo->setPublicationId('PublicationIdValue');
        $publicationInfo->setUsagePolicy([
            'en' => 'http://EnglishUsagePolicy',
            'no' => 'http://NorwegianUsagePolicy',
        ]);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $publicationInfo->toXML($document->firstChild);

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

        $this->assertEquals('en', $usagePolicyElements[0]->getAttributeNS("http://www.w3.org/XML/1998/namespace", "lang"));
        $this->assertEquals('http://EnglishUsagePolicy', $usagePolicyElements[0]->textContent);
        $this->assertEquals('no', $usagePolicyElements[1]->getAttributeNS("http://www.w3.org/XML/1998/namespace", "lang"));
        $this->assertEquals('http://NorwegianUsagePolicy', $usagePolicyElements[1]->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdrpi:PublicationInfo xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                       publisher="SomePublisher"
                       creationInstant="2011-01-01T00:00:00Z"
                       publicationId="SomePublicationId">
    <mdrpi:UsagePolicy xml:lang="en">http://TheEnglishUsagePolicy</mdrpi:UsagePolicy>
    <mdrpi:UsagePolicy xml:lang="no">http://TheNorwegianUsagePolicy</mdrpi:UsagePolicy>
</mdrpi:PublicationInfo>
XML
        );

        $publicationInfo = new PublicationInfo($document->firstChild);

        $this->assertEquals('SomePublisher', $publicationInfo->getPublisher());
        $this->assertEquals(1293840000, $publicationInfo->getCreationInstant());
        $this->assertEquals('SomePublicationId', $publicationInfo->getPublicationId());

        $usagePolicy = $publicationInfo->getUsagePolicy();
        $this->assertCount(2, $usagePolicy);
        $this->assertEquals('http://TheEnglishUsagePolicy', $usagePolicy["en"]);
        $this->assertEquals('http://TheNorwegianUsagePolicy', $usagePolicy["no"]);
    }


    public function testMissingPublisherThrowsException()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdrpi:PublicationInfo xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                       creationInstant="2011-01-01T00:00:00Z"
                       publicationId="SomePublicationId">
</mdrpi:PublicationInfo>
XML
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing required attribute "publisher"');
        $publicationInfo = new PublicationInfo($document->firstChild);
    }
}
