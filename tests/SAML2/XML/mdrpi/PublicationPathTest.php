<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\PublicationPath;
use SimpleSAML\SAML2\XML\mdrpi\Publication;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\mdrpi\PublicationPathTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\PublicationPath
 * @covers \SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement
 * @package simplesamlphp/saml2
 */
final class PublicationPathTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = PublicationPath::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdrpi_PublicationPath.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $publicationPath = new PublicationPath(
            [
                New Publication('SomePublisher', 1234567890, 'SomePublicationId'),
                New Publication('SomeOtherPublisher', 1234567890, 'SomeOtherPublicationId'),
            ]
        );

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $publicationPath->toXML($document->documentElement);

        /** @var \DOMElement[] $publicationInfoElements */
        $publicationPathElements = XMLUtils::xpQuery(
            $xml,
            '/root/*[local-name()=\'PublicationPath\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:rpi\']'
        );
        $this->assertCount(1, $publicationPathElements);
        $publicationPathElement = $publicationPathElements[0];

        /** @var \DOMElement[] $publicationElements */
        $publicationElements = XMLUtils::xpQuery(
            $publicationPathElement,
            './*[local-name()=\'Publication\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:rpi\']'
        );
        $this->assertCount(2, $publicationElements);

        $this->assertEquals('SomePublisher', $publicationElements[0]->getAttribute("publisher"));
        $this->assertEquals('2009-02-13T23:31:30Z', $publicationElements[0]->getAttribute("creationInstant"));
        $this->assertEquals('SomePublicationId', $publicationElements[0]->getAttribute("publicationId"));
        $this->assertEquals('SomeOtherPublisher', $publicationElements[1]->getAttribute("publisher"));
        $this->assertEquals('2009-02-13T23:31:30Z', $publicationElements[1]->getAttribute("creationInstant"));
        $this->assertEquals('SomeOtherPublicationId', $publicationElements[1]->getAttribute("publicationId"));
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $publicationPath = PublicationPath::fromXML($this->xmlRepresentation->documentElement);

        $publication = $publicationPath->getPublication();
        $this->assertCount(2, $publication);

        $this->assertEquals('SomePublisher', $publication[0]->getPublisher());
        $this->assertEquals(1293840000, $publication[0]->getCreationInstant());
        $this->assertEquals('SomePublicationId', $publication[0]->getPublicationId());
        $this->assertEquals('SomeOtherPublisher', $publication[1]->getPublisher());
        $this->assertEquals(1293840000, $publication[1]->getCreationInstant());
        $this->assertEquals('SomeOtherPublicationId', $publication[1]->getPublicationId());
    }
}
