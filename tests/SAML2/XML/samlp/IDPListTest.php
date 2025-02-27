<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, EntityIDValue, SAMLStringValue};
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\samlp\{AbstractSamlpElement, GetComplete, IDPEntry, IDPList};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\TestUtils\{ArrayizableElementTestTrait, SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\IDPListTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(IDPList::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class IDPListTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = IDPList::class;

        self::$arrayRepresentation = [
            'IDPEntry' => [
                ['ProviderID' => 'urn:some:requester1', 'Name' => 'testName1', 'Loc' => 'urn:test:testLoc1'],
                ['ProviderID' => 'urn:some:requester2', 'Name' => 'testName2', 'Loc' => 'urn:test:testLoc2'],
            ],
            'GetComplete' => ['https://some/location'],
        ];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_IDPList.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $entry1 = new IDPEntry(
            EntityIDValue::fromString('urn:some:requester1'),
            SAMLStringValue::fromString('testName1'),
            SAMLAnyURIValue::fromString('urn:test:testLoc1'),
        );
        $entry2 = new IDPEntry(
            EntityIDValue::fromString('urn:some:requester2'),
            SAMLStringValue::fromString('testName2'),
            SAMLAnyURIValue::fromString('urn:test:testLoc2'),
        );
        $getComplete = new GetComplete(
            SAMLAnyURIValue::fromString('https://some/location'),
        );
        $list = new IDPList([$entry1, $entry2], $getComplete);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($list),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $entry1 = new IDPEntry(
            EntityIDValue::fromString('urn:some:requester1'),
            SAMLStringValue::fromString('testName1'),
            SAMLAnyURIValue::fromString('urn:test:testLoc1'),
        );
        $entry2 = new IDPEntry(
            EntityIDValue::fromString('urn:some:requester2'),
            SAMLStringValue::fromString('testName2'),
            SAMLAnyURIValue::fromString('urn:test:testLoc2'),
        );
        $getComplete = new GetComplete(
            SAMLAnyURIValue::fromString('https://some/location'),
        );
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
  <samlp:IDPEntry ProviderID="urn:some:requester1" Name="testName1" Loc="urn:test:testLoc1"/>
</samlp:IDPList>
XML
        ;

        $entry1 = new IDPEntry(
            EntityIDValue::fromString('urn:some:requester1'),
            SAMLStringValue::fromString('testName1'),
            SAMLAnyURIValue::fromString('urn:test:testLoc1'),
        );
        $list = new IDPList([$entry1]);

        $entries = $list->getIdpEntry();
        $this->assertCount(1, $entries);

        $this->assertEquals('urn:some:requester1', $entries[0]->getProviderID());
        $this->assertEquals('testName1', $entries[0]->getName());
        $this->assertEquals('urn:test:testLoc1', $entries[0]->getLoc());

        $this->assertNull($list->getGetComplete());

        $this->assertEquals($document, strval($list));
    }


    /**
     */
    public function testZeroIDPEntriesThrowsException(): void
    {
        $ns = IDPList::NS;

        $xmlRepresentation = DOMDocumentFactory::fromString(
            <<<XML
<samlp:IDPList xmlns:samlp="{$ns}">
  <samlp:GetComplete>https://some/location</samlp:GetComplete>
</samlp:IDPList>
XML
            ,
        );

        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('At least one <samlp:IDPEntry> must be specified.');

        IDPList::fromXML($xmlRepresentation->documentElement);
    }
}
