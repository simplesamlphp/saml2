<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\{AbstractMdElement, SingleSignOnService};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Tests for md:SingleSignOnService.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(SingleSignOnService::class)]
#[CoversClass(AbstractMdElement::class)]
final class SingleSignOnServiceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
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
        $ssoep = new SingleSignOnService(
            SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
            SAMLAnyURIValue::fromString(C::LOCATION_A),
        );

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
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:SingleSignOnService.',
        );

        new SingleSignOnService(
            SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
            SAMLAnyURIValue::fromString(C::LOCATION_A),
            SAMLAnyURIValue::fromString('https://response.location/'),
        );
    }


    // test unmarshalling


    /**
     * Test that creating a SingleSignOnService from XML fails when ResponseLocation is present.
     */
    public function testUnmarshallingWithResponseLocation(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('ResponseLocation', 'https://response.location/');

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:SingleSignOnService.',
        );

        SingleSignOnService::fromXML($xmlRepresentation->documentElement);
    }
}
