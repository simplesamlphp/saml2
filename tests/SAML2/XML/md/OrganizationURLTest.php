<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\OrganizationURL;
use SimpleSAML\Test\XML\ArrayizableElementTestTrait;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\SchemaViolationException;

use function dirname;
use function strval;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\md\OrganizationURL
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedURI
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 *
 * @package simplesamlphp/saml2
 */
final class OrganizationURLTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = OrganizationURL::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_OrganizationURL.xml',
        );

        $this->arrayRepresentation = ['en' => 'https://IdentityProvider.com'];
    }


    // test marshalling


    /**
     * Test creating a OrganizationURL object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new OrganizationURL('en', 'https://IdentityProvider.com');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name),
        );
    }


    // test unmarshalling


    /**
     * Test creating a OrganizationURL from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = OrganizationURL::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name),
        );
    }


    /**
     * Test that creating a OrganizationURL with an invalid url throws an exception
     */
    public function testUnmarshallingFailsInvalidURL(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->textContent = 'this is no url';

        $this->expectException(SchemaViolationException::class);
        OrganizationURL::fromXML($document->documentElement);
    }
}
