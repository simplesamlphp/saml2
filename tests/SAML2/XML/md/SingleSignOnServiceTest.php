<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\SingleSignOnService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

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
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = SingleSignOnService::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_SingleSignOnService.xml'
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
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($ssoep)
        );
    }


    /**
     * Test that creating a SingleSignOnService from scratch with a ResponseLocation fails.
     */
    public function testMarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:SingleSignOnService.'
        );

        new SingleSignOnService(C::BINDING_HTTP_POST, C::LOCATION_A, 'https://response.location/');
    }


    // test unmarshalling


    /**
     * Test creating a SingleSignOnService from XML.
     */
    public function testUnmarshalling(): void
    {
        $ssoep = SingleSignOnService::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(C::BINDING_HTTP_POST, $ssoep->getBinding());
        $this->assertEquals(C::LOCATION_A, $ssoep->getLocation());
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($ssoep)
        );
    }


    /**
     * Test that creating a SingleSignOnService from XML fails when ResponseLocation is present.
     */
    public function testUnmarshallingWithResponseLocation(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('ResponseLocation', 'https://response.location/');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:SingleSignOnService.'
        );

        SingleSignOnService::fromXML($this->xmlRepresentation->documentElement);
    }
}
