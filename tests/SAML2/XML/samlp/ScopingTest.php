<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\samlp\GetComplete;
use SimpleSAML\SAML2\XML\samlp\IDPEntry;
use SimpleSAML\SAML2\XML\samlp\IDPList;
use SimpleSAML\SAML2\XML\samlp\RequesterID;
use SimpleSAML\SAML2\XML\samlp\Scoping;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\samlp\ScopingTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\Scoping
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class ScopingTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = Scoping::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_Scoping.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'testLoc1');
        $getComplete = new GetComplete('https://some/location');
        $list = new IDPList([$entry1], $getComplete);
        $requesterId = 'urn:some:requester';

        $scoping = new Scoping(2, $list, [new RequesterID($requesterId)]);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($scoping)
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'testLoc1');
        $getComplete = new GetComplete('https://some/location');
        $list = new IDPList([$entry1], $getComplete);
        $requesterId = 'urn:some:requester';

        $scoping = new Scoping(2, $list, [new RequesterID($requesterId)]);

        $scopingElement = $scoping->toXML();

        // Test for an IDPList
        $scopingElements = XMLUtils::xpQuery($scopingElement, './saml_protocol:IDPList');
        $this->assertCount(1, $scopingElements);

        // Test ordering of Scoping contents
        /** @psalm-var \DOMElement[] $scopingElements */
        $scopingElements = XMLUtils::xpQuery($scopingElement, './saml_protocol:IDPList/following-sibling::*');
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
     */
    public function testUnmarshalling(): void
    {
        $scoping = Scoping::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(2, $scoping->getProxyCount());

        $list = $scoping->getIDPList();
        $this->assertInstanceOf(IDPList::class, $list);

        $entries = $list->getIdpEntry();
        $this->assertCount(1, $entries);

        $this->assertEquals('urn:some:requester1', $entries[0]->getProviderID());
        $this->assertEquals('testName1', $entries[0]->getName());
        $this->assertEquals('testLoc1', $entries[0]->getLoc());

        $this->assertEquals('https://some/location', $list->getGetComplete()->getContent());

        $requesterId = $scoping->getRequesterId();
        $this->assertCount(1, $requesterId);
        $this->assertEquals('urn:some:requester', $requesterId[0]->getContent());
    }
}
