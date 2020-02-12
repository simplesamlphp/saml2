<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Tests for md:SingleSignOnService.
 */
final class SingleSignOnServiceTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $mdNamespace = Constants::NS_MD;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:SingleSignOnService xmlns:md="{$mdNamespace}" Binding="urn:something" Location="https://whatever/" />
XML
        );
    }


    // test marshalling


    /**
     * Test creating a SingleSignOnService from scratch.
     */
    public function testMarshalling(): void
    {
        $ssoep = new SingleSignOnService('urn:something', 'https://whatever/');

        $this->assertEquals('urn:something', $ssoep->getBinding());
        $this->assertEquals('https://whatever/', $ssoep->getLocation());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($ssoep));
    }


    /**
     * Test that creating a SingleSignOnService from scratch with a ResponseLocation fails.
     */
    public function testMarshallingWithResponseLocation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The \'ResponseLocation\' attribute must be omitted for md:SingleSignOnService.');

        new SingleSignOnService('urn:something', 'https://whatever/', 'https://response.location/');
    }


    // test unmarshalling


    /**
     * Test creating a SingleSignOnService from XML.
     */
    public function testUnmarshalling(): void
    {
        $ssoep = SingleSignOnService::fromXML($this->document->documentElement);

        $this->assertEquals('urn:something', $ssoep->getBinding());
        $this->assertEquals('https://whatever/', $ssoep->getLocation());
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($ssoep));
    }


    /**
     * Test that creating a SingleSignOnService from XML fails when ResponseLocation is present.
     */
    public function testUnmarshallingWithResponseLocation(): void
    {
        $this->document->documentElement->setAttribute('ResponseLocation', 'https://response.location/');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The \'ResponseLocation\' attribute must be omitted for md:SingleSignOnService.');

        SingleSignOnService::fromXML($this->document->documentElement);
    }


    /**
     * Test that serialization / unserialization works.
     */
    public function testSerialization(): void
    {
        $ep = SingleSignOnService::fromXML($this->document->documentElement);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize($ep)))
        );
    }
}
