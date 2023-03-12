<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\mdrpi\UsagePolicy;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\UsagePolicy
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedURI
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 *
 * @package simplesamlphp/saml2
 */
final class UsagePolicyTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-metadata-rpi-v1.0.xsd';

        $this->testedClass = UsagePolicy::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/mdrpi_UsagePolicy.xml',
        );

        $this->arrayRepresentation = ['en' => 'http://www.example.edu/en/'];
    }


    // test marshalling


    /**
     * Test creating a UsagePolicy object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new UsagePolicy('en', 'http://www.example.edu/en/');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name),
        );
    }


    // test unmarshalling


    /**
     * Test creating a UsagePolicy from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = UsagePolicy::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name),
        );
    }


    /**
     * Test that creating a UsagePolicy with an invalid url throws an exception
     */
    public function testUnmarshallingFailsInvalidURL(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->textContent = 'this is no url';

        $this->expectException(SchemaViolationException::class);
        UsagePolicy::fromXML($document->documentElement);
    }
}
