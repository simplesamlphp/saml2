<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\IDPEntry;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\IDPEntryTest
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(IDPEntry::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class IDPEntryTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = IDPentry::class;

        self::$arrayRepresentation = [
            'ProviderID' => 'urn:some:requester',
            'Name' => 'testName',
            'Loc' => 'urn:test:testLoc',
        ];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_IDPEntry.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $entry = new IDPEntry('urn:some:requester', 'testName', 'urn:test:testLoc');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($entry),
        );
    }


    /**
     */
    public function testMarshallingNullables(): void
    {
        $document = clone self::$xmlRepresentation;
        $document->documentElement->removeAttribute('Name');
        $document->documentElement->removeAttribute('Loc');

        $entry = new IDPEntry('urn:some:requester');

        $this->assertEquals('urn:some:requester', $entry->getProviderID());
        $this->assertNull($entry->getName());
        $this->assertNull($entry->getLoc());

        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($entry),
        );
    }
}
