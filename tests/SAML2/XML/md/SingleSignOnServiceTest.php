<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\SingleSignOnService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for md:SingleSignOnService.
 *
 * @covers \SimpleSAML\SAML2\XML\md\SingleSignOnService
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class SingleSignOnServiceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = SingleSignOnService::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_SingleSignOnService.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a SingleSignOnService from scratch.
     */
    public function testMarshalling(): void
    {
        $ssoep = new SingleSignOnService(C::BINDING_HTTP_POST, C::LOCATION_A);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($ssoep),
        );
    }


    /**
     * Test that creating a SingleSignOnService from scratch with a ResponseLocation fails.
     */
    public function testMarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:SingleSignOnService.',
        );

        new SingleSignOnService(C::BINDING_HTTP_POST, C::LOCATION_A, 'https://response.location/');
    }


    // test unmarshalling


    /**
     * Test that creating a SingleSignOnService from XML fails when ResponseLocation is present.
     */
    public function testUnmarshallingWithResponseLocation(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('ResponseLocation', 'https://response.location/');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:SingleSignOnService.',
        );

        SingleSignOnService::fromXML($xmlRepresentation->documentElement);
    }
}
