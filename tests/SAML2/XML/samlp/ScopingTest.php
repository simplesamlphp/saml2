<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\samlp\IDPEntry;
use SimpleSAML\SAML2\XML\samlp\IDPList;
use SimpleSAML\SAML2\XML\samlp\Scoping;

/**
 * Class \SAML2\XML\samlp\ScopingTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\Scoping
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
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_Scoping.xml'
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
     * @return void
     */
    public function testMarshallingElementOrdering(): void
    {
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'testLoc1');
        $getComplete = 'https://some/location';
        $list = new IDPList([$entry1], $getComplete);
        $requesterId = 'urn:some:requester';

        $scoping = new Scoping(2, $list, [$requesterId]);

        $scopingElement = $scoping->toXML();

        // Test for an IDPList
        $scopingElements = Utils::xpQuery($scopingElement, './saml_protocol:IDPList');
        $this->assertCount(1, $scopingElements);

        // Test ordering of Scoping contents
        $scopingElements = Utils::xpQuery($scopingElement, './saml_protocol:IDPList/following-sibling::*');
        $this->assertCount(1, $scopingElements);
        $this->assertEquals('samlp:RequesterID', $scopingElements[0]->tagName);
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
