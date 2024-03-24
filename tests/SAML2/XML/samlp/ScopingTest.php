<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\GetComplete;
use SimpleSAML\SAML2\XML\samlp\IDPEntry;
use SimpleSAML\SAML2\XML\samlp\IDPList;
use SimpleSAML\SAML2\XML\samlp\RequesterID;
use SimpleSAML\SAML2\XML\samlp\Scoping;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\ScopingTest
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(Scoping::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class ScopingTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = Scoping::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_Scoping.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'urn:test:testLoc1');
        $getComplete = new GetComplete('https://some/location');
        $list = new IDPList([$entry1], $getComplete);
        $requesterId = 'urn:some:requester';

        $scoping = new Scoping(2, $list, [new RequesterID($requesterId)]);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($scoping),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'urn:test:testLoc1');
        $getComplete = new GetComplete('https://some/location');
        $list = new IDPList([$entry1], $getComplete);
        $requesterId = 'urn:some:requester';

        $scoping = new Scoping(2, $list, [new RequesterID($requesterId)]);

        $scopingElement = $scoping->toXML();

        // Test for an IDPList
        $xpCache = XPath::getXPath($scopingElement);
        /** @psalm-var \DOMElement[] $scopingElements */
        $scopingElements = XPath::xpQuery($scopingElement, './saml_protocol:IDPList', $xpCache);
        $this->assertCount(1, $scopingElements);

        // Test ordering of Scoping contents
        /** @psalm-var \DOMElement[] $scopingElements */
        $scopingElements = XPath::xpQuery($scopingElement, './saml_protocol:IDPList/following-sibling::*', $xpCache);
        $this->assertCount(1, $scopingElements);
        $this->assertEquals('samlp:RequesterID', $scopingElements[0]->tagName);
    }


    /**
     * Adding no contents to a Scoping element should yield an empty element. If there were contents already
     * there, those should be left untouched.
     */
    public function testMarshallingWithNoElements(): void
    {
        $samlpns = C::NS_SAMLP;
        $scoping = new Scoping();
        $this->assertEquals(
            "<samlp:Scoping xmlns:samlp=\"$samlpns\"/>",
            strval($scoping),
        );
        $this->assertTrue($scoping->isEmptyElement());
    }
}
