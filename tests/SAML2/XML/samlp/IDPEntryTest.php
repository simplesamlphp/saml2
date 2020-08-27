<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SAML2\DOMDocumentFactory;
use SAML2\XML\samlp\IDPEntry;

/**
 * Class \SAML2\XML\samlp\IDPEntryTest
 *
 * @covers \SAML2\XML\samlp\IDPEntry
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class IDPEntryTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $ns = IDPEntry::NS;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:IDPEntry xmlns:samlp="{$ns}" ProviderID="urn:some:requester" Name="testName" Loc="testLoc"/>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $entry = new IDPEntry('urn:some:requester', 'testName', 'testLoc');

        $this->assertEquals('urn:some:requester', $entry->getProviderID());
        $this->assertEquals('testName', $entry->getName());
        $this->assertEquals('testLoc', $entry->getLoc());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($entry));
    }

    /**
     * @return void
     */
    public function testMarshallingNullables(): void
    {
        $ns = IDPEntry::NS;
        $document = <<<XML
<samlp:IDPEntry xmlns:samlp="{$ns}" ProviderID="urn:some:requester"/>
XML
        ;

        $entry = new IDPEntry('urn:some:requester', null, null);

        $this->assertEquals('urn:some:requester', $entry->getProviderID());
        $this->assertNull($entry->getName());
        $this->assertNull($entry->getLoc());

        $this->assertEquals($document, strval($entry));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $entry = IDPEntry::fromXML($this->document->documentElement);

        $this->assertEquals('urn:some:requester', $entry->getProviderID());
        $this->assertEquals('testName', $entry->getName());
        $this->assertEquals('testLoc', $entry->getLoc());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(IDPEntry::fromXML($this->document->documentElement))))
        );
    }
}
