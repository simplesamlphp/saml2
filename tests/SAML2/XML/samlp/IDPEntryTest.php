<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\IDPEntry;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\samlp\IDPEntryTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\IDPEntry
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class IDPEntryTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = IDPentry::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_IDPEntry.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $entry = new IDPEntry('urn:some:requester', 'testName', 'testLoc');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($entry)
        );
    }

    /**
     */
    public function testMarshallingNullables(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->removeAttribute('Name');
        $document->documentElement->removeAttribute('Loc');

        $entry = new IDPEntry('urn:some:requester', null, null);

        $this->assertEquals('urn:some:requester', $entry->getProviderID());
        $this->assertNull($entry->getName());
        $this->assertNull($entry->getLoc());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($document->documentElement),
            strval($entry)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $entry = IDPEntry::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('urn:some:requester', $entry->getProviderID());
        $this->assertEquals('testName', $entry->getName());
        $this->assertEquals('testLoc', $entry->getLoc());
    }
}
