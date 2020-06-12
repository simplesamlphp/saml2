<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\samlp\IDPEntry;
use SAML2\XML\samlp\IDPList;
use SAML2\XML\samlp\Scoping;

/**
 * Class \SAML2\XML\samlp\ScopingTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class ScopingTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $ns = Scoping::NS;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:Scoping xmlns:samlp="{$ns}" ProxyCount="2">
  <samlp:IDPList>
    <samlp:IDPEntry ProviderID="urn:some:requester1" Name="testName1" Loc="testLoc1"/>
    <samlp:GetComplete>https://some/location</samlp:GetComplete>
  </samlp:IDPList>
  <samlp:RequesterID>urn:some:requester</samlp:RequesterID>
</samlp:Scoping>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'testLoc1');
        $getComplete = 'https://some/location';
        $list = new IDPList([$entry1], $getComplete);
        $requesterId = 'urn:some:requester';

        $scoping = new Scoping(2, $list, [$requesterId]);
        $this->assertEquals(2, $scoping->getProxyCount());

        $list = $scoping->getIDPList();
        $this->assertInstanceOf(IDPList::class, $list);

        $entries = $list->getIdpEntry();
        $this->assertCount(1, $entries);

        $this->assertEquals('urn:some:requester1', $entries[0]->getProviderID());
        $this->assertEquals('testName1', $entries[0]->getName());
        $this->assertEquals('testLoc1', $entries[0]->getLoc());

        $this->assertEquals('https://some/location', $list->getGetComplete());

        $requesterId = $scoping->getRequesterId();
        $this->assertCount(1, $requesterId);
        $this->assertEquals('urn:some:requester', $requesterId[0]);

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($scoping));
    }


    /**
     * Adding no contents to a Scoping element should yield an empty element. If there were contents already
     * there, those should be left untouched.
     */
    public function testMarshallingWithNoElements(): void
    {
        $samlpns = Constants::NS_SAMLP;
        $scoping = new Scoping();
        $this->assertEquals(
            "<samlp:Scoping xmlns:samlp=\"$samlpns\"/>",
            strval($scoping)
        );
        $this->assertTrue($scoping->isEmptyElement());
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $scoping = Scoping::fromXML($this->document->documentElement);
        $this->assertEquals(2, $scoping->getProxyCount());

        $list = $scoping->getIDPList();
        $this->assertInstanceOf(IDPList::class, $list);

        $entries = $list->getIdpEntry();
        $this->assertCount(1, $entries);

        $this->assertEquals('urn:some:requester1', $entries[0]->getProviderID());
        $this->assertEquals('testName1', $entries[0]->getName());
        $this->assertEquals('testLoc1', $entries[0]->getLoc());

        $this->assertEquals('https://some/location', $list->getGetComplete());

        $requesterId = $scoping->getRequesterId();
        $this->assertCount(1, $requesterId);
        $this->assertEquals('urn:some:requester', $requesterId[0]);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Scoping::fromXML($this->document->documentElement))))
        );
    }
}
