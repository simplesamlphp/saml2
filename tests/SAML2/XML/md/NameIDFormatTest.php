<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\NameIDFormat;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\md\NameIDFormatTest
 *
 * @covers \SimpleSAML\SAML2\XML\md\NameIDFormat
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class NameIDFormatTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = NameIDFormat::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_NameIDFormat.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $nameIdFormat = new NameIDFormat(C::NAMEID_PERSISTENT);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($nameIdFormat)
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $nameIdFormat = NameIDFormat::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($nameIdFormat)
        );
    }
}
