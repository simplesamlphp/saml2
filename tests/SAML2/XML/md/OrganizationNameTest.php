<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\OrganizationName;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\md\OrganizationName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class OrganizationNameTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = OrganizationName::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/md_OrganizationName.xml',
        );

        $this->arrayRepresentation = ['en' => 'Identity Providers R US'];
    }


    // test marshalling


    /**
     * Test creating a OrganizationName object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new OrganizationName('en', 'Identity Providers R US');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name),
        );
    }


    // test unmarshalling


    /**
     * Test creating a OrganizationName from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = OrganizationName::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name),
        );
    }
}
