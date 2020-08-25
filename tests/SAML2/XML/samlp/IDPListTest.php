<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SAML2\DOMDocumentFactory;
use SAML2\Exception\MissingElementException;
use SAML2\Utils;
use SAML2\XML\samlp\IDPEntry;
use SAML2\XML\samlp\IDPList;

/**
 * Class \SAML2\XML\samlp\IDPListTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class IDPListTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $ns = IDPList::NS;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:IDPList xmlns:samlp="{$ns}">
  <samlp:IDPEntry ProviderID="urn:some:requester1" Name="testName1" Loc="testLoc1"/>
  <samlp:IDPEntry ProviderID="urn:some:requester2" Name="testName2" Loc="testLoc2"/>
  <samlp:GetComplete>https://some/location</samlp:GetComplete>
</samlp:IDPList>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'testLoc1');
        $entry2 = new IDPEntry('urn:some:requester2', 'testName2', 'testLoc2');
        $getComplete = 'https://some/location';
        $list = new IDPList([$entry1, $entry2], $getComplete);

        $entries = $list->getIdpEntry();
        $this->assertCount(2, $entries);

        $this->assertEquals('urn:some:requester1', $entries[0]->getProviderID());
        $this->assertEquals('testName1', $entries[0]->getName());
        $this->assertEquals('testLoc1', $entries[0]->getLoc());

        $this->assertEquals('urn:some:requester2', $entries[1]->getProviderID());
        $this->assertEquals('testName2', $entries[1]->getName());
        $this->assertEquals('testLoc2', $entries[1]->getLoc());

        $this->assertEquals('https://some/location', $list->getGetComplete());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($list));
    }


    /**
     * @return void
     */
    public function testMarshallingElementOrdering(): void
    {
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'testLoc1');
        $entry2 = new IDPEntry('urn:some:requester2', 'testName2', 'testLoc2');
        $getComplete = 'https://some/location';
        $list = new IDPList([$entry1, $entry2], $getComplete);

        $listElement = $list->toXML();

        // Test for an IDPEntry
        $listElements = Utils::xpQuery($listElement, './saml_protocol:IDPEntry');
        $this->assertCount(2, $listElements);

        // Test ordering of IDPList contents
        $listElements = Utils::xpQuery($listElement, './saml_protocol:IDPEntry/following-sibling::*');
        $this->assertCount(2, $listElements);
        $this->assertEquals('samlp:IDPEntry', $listElements[0]->tagName);
        $this->assertEquals('samlp:GetComplete', $listElements[1]->tagName);
    }


    /**
     * @return void
     */
    public function testMarshallingNullables(): void
    {
        $ns = IDPList::NS;
        $document = <<<XML
<samlp:IDPList xmlns:samlp="{$ns}">
  <samlp:IDPEntry ProviderID="urn:some:requester1" Name="testName1" Loc="testLoc1"/>
</samlp:IDPList>
XML
        ;

        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'testLoc1');
        $list = new IDPList([$entry1], null);

        $entries = $list->getIdpEntry();
        $this->assertCount(1, $entries);

        $this->assertEquals('urn:some:requester1', $entries[0]->getProviderID());
        $this->assertEquals('testName1', $entries[0]->getName());
        $this->assertEquals('testLoc1', $entries[0]->getLoc());

        $this->assertNull($list->getGetComplete());

        $this->assertEquals($document, strval($list));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $list = IDPList::fromXML($this->document->documentElement);

        $entries = $list->getIdpEntry();
        $this->assertCount(2, $entries);

        $this->assertEquals('urn:some:requester1', $entries[0]->getProviderID());
        $this->assertEquals('testName1', $entries[0]->getName());
        $this->assertEquals('testLoc1', $entries[0]->getLoc());

        $this->assertEquals('urn:some:requester2', $entries[1]->getProviderID());
        $this->assertEquals('testName2', $entries[1]->getName());
        $this->assertEquals('testLoc2', $entries[1]->getLoc());

        $this->assertEquals('https://some/location', $list->getGetComplete());
    }


    /**
     * @return void
     */
    public function testZeroIDPEntriesThrowsException(): void
    {
        $ns = IDPList::NS;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:IDPList xmlns:samlp="{$ns}">
  <samlp:GetComplete>https://some/location</samlp:GetComplete>
</samlp:IDPList>
XML
        );

        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('At least one <samlp:IDPEntry> must be specified.');

        IDPList::fromXML($this->document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(IDPList::fromXML($this->document->documentElement))))
        );
    }
}
