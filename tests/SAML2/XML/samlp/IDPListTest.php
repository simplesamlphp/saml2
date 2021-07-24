<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\samlp\GetComplete;
use SimpleSAML\SAML2\XML\samlp\IDPEntry;
use SimpleSAML\SAML2\XML\samlp\IDPList;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingElementException;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\samlp\IDPListTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\IDPList
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class IDPListTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = IDPList::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_IDPList.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'testLoc1');
        $entry2 = new IDPEntry('urn:some:requester2', 'testName2', 'testLoc2');
        $getComplete = new GetComplete('https://some/location');
        $list = new IDPList([$entry1, $entry2], $getComplete);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($list)
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'testLoc1');
        $entry2 = new IDPEntry('urn:some:requester2', 'testName2', 'testLoc2');
        $getComplete = new GetComplete('https://some/location');
        $list = new IDPList([$entry1, $entry2], $getComplete);

        $listElement = $list->toXML();

        // Test for an IDPEntry
        $xpCache = XPath::getXPath($listElement);
        $listElements = XPath::xpQuery($listElement, './saml_protocol:IDPEntry', $xpCache);
        $this->assertCount(2, $listElements);

        // Test ordering of IDPList contents
        /** @psalm-var \DOMElement[] $listElements */
        $listElements = XPath::xpQuery($listElement, './saml_protocol:IDPEntry/following-sibling::*', $xpCache);
        $this->assertCount(2, $listElements);
        $this->assertEquals('samlp:IDPEntry', $listElements[0]->tagName);
        $this->assertEquals('samlp:GetComplete', $listElements[1]->tagName);
    }


    /**
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
     */
    public function testUnmarshalling(): void
    {
        $list = IDPList::fromXML($this->xmlRepresentation->documentElement);

        $entries = $list->getIdpEntry();
        $this->assertCount(2, $entries);

        $this->assertEquals('urn:some:requester1', $entries[0]->getProviderID());
        $this->assertEquals('testName1', $entries[0]->getName());
        $this->assertEquals('testLoc1', $entries[0]->getLoc());

        $this->assertEquals('urn:some:requester2', $entries[1]->getProviderID());
        $this->assertEquals('testName2', $entries[1]->getName());
        $this->assertEquals('testLoc2', $entries[1]->getLoc());

        $this->assertEquals('https://some/location', $list->getGetComplete()->getContent());
    }


    /**
     */
    public function testZeroIDPEntriesThrowsException(): void
    {
        $ns = IDPList::NS;

        $this->xmlRepresentation = DOMDocumentFactory::fromString(<<<XML
<samlp:IDPList xmlns:samlp="{$ns}">
  <samlp:GetComplete>https://some/location</samlp:GetComplete>
</samlp:IDPList>
XML
        );

        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('At least one <samlp:IDPEntry> must be specified.');

        IDPList::fromXML($this->xmlRepresentation->documentElement);
    }
}
