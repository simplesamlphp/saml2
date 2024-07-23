<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\AdditionalMetadataLocation;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for the AdditionalMetadataLocation class
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(AdditionalMetadataLocation::class)]
#[CoversClass(AbstractMdElement::class)]
final class AdditionalMetadataLocationTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = AdditionalMetadataLocation::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AdditionalMetadataLocation.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an AdditionalMetadataLocation object from scratch.
     */
    public function testMarshalling(): void
    {
        $additionalMetadataLocation = new AdditionalMetadataLocation(C::NAMESPACE, C::LOCATION_A);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($additionalMetadataLocation),
        );
    }


    /**
     * Test that creating an AdditionalMetadataLocation from scratch with an empty namespace fails.
     */
    public function testMarshallingWithEmptyNamespace(): void
    {
        $this->expectException(ProtocolViolationException::class);
        new AdditionalMetadataLocation('', C::LOCATION_A);
    }


    // test unmarshalling


    /**
     * Test that creating an AdditionalMetadataLocation from XML fails if "namespace" is missing.
     */
    public function testUnmarshallingWithoutNamespace(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttribute('namespace');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'namespace' attribute on md:AdditionalMetadataLocation.");
        AdditionalMetadataLocation::fromXML($document);
    }
}
