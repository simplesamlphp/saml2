<?php

namespace SAML2\XML\mdrpi;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\mdrpi\PublicationInfoTest
 */
class PublicationInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $publicationInfo = new PublicationInfo();
        $publicationInfo->publisher = 'TestPublisher';
        $publicationInfo->creationInstant = 1234567890;
        $publicationInfo->publicationId = 'PublicationIdValue';
        $publicationInfo->UsagePolicy = array(
            'en' => 'http://EnglishUsagePolicy',
            'no' => 'http://NorwegianUsagePolicy',
        );

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $publicationInfo->toXML($document->firstChild);

        $publicationInfoElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'PublicationInfo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:rpi\']'
        );
        $this->assertCount(1, $publicationInfoElements);
        $publicationInfoElement = $publicationInfoElements[0];

        $this->assertEquals('TestPublisher', $publicationInfoElement->getAttribute("publisher"));
        $this->assertEquals('2009-02-13T23:31:30Z', $publicationInfoElement->getAttribute("creationInstant"));
        $this->assertEquals('PublicationIdValue', $publicationInfoElement->getAttribute("publicationId"));

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

    public function testUnmarshalling()
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

        $this->assertEquals('SomePublisher', $publicationInfo->publisher);
        $this->assertEquals(1293840000, $publicationInfo->creationInstant);
        $this->assertEquals('SomePublicationId', $publicationInfo->publicationId);
        $this->assertCount(2, $publicationInfo->UsagePolicy);
        $this->assertEquals('http://TheEnglishUsagePolicy', $publicationInfo->UsagePolicy["en"]);
        $this->assertEquals('http://TheNorwegianUsagePolicy', $publicationInfo->UsagePolicy["no"]);
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

        $this->setExpectedException('Exception', 'Missing required attribute "publisher"');
        $publicationInfo = new PublicationInfo($document->firstChild);
    }
}
