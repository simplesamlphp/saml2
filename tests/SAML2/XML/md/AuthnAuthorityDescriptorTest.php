<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

final class AuthnAuthorityDescriptorTest extends TestCase
{
    /** @var \SAML2\XML\md\AssertionIDRequestService */
    protected $aidrs;

    /** @var \SAML2\XML\md\AuthnQueryService */
    protected $aqs;

    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:AuthnAuthorityDescriptor xmlns:md="${mdns}" protocolSupportEnumeration="protocol1 protocol2">
  <md:AuthnQueryService Binding="uri:binding:aqs" Location="http://www.example.com/aqs" />
  <md:AssertionIDRequestService Binding="uri:binding:aidrs" Location="http://www.example.com/aidrs" />
  <md:NameIDFormat>http://www.example1.com/</md:NameIDFormat>
  <md:NameIDFormat>http://www.example2.com/</md:NameIDFormat>
</md:AuthnAuthorityDescriptor>
XML
        );

        $this->aqs = new AuthnQueryService('uri:binding:aqs', 'http://www.example.com/aqs');
        $this->aidrs = new AssertionIDRequestService('uri:binding:aidrs', 'http://www.example.com/aidrs');
    }


    // test marshalling


    /**
     * Test creating an AuthnAuthorityDescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $aad = new AuthnAuthorityDescriptor(
            [$this->aqs],
            ['protocol1', 'protocol2'],
            [$this->aidrs],
            ['http://www.example1.com/', 'http://www.example2.com/']
        );
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($aad)
        );
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor without AuthnQueryService elements fails.
     */
    public function testMarshallingWithoutAuthnQueryServices(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing at least one AuthnQueryService in AuthnAuthorityDescriptor.');
        new AuthnAuthorityDescriptor(
            [],
            ['protocol1', 'protocol2'],
            [$this->aidrs],
            ['http://www.example1.com/', 'http://www.example2.com/']
        );
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor without optional elements works.
     */
    public function testMarshallingWithoutOptionalElements(): void
    {
        new AuthnAuthorityDescriptor(
            [$this->aqs],
            ['protocol1', 'protocol2']
        );

        $this->assertTrue(true);
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor with an empty NameIDFormat fails.
     */
    public function testMarshallWithEmptyNameIDFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('NameIDFormat cannot be an empty string.');
        new AuthnAuthorityDescriptor(
            [$this->aqs],
            ['protocol1', 'protocol2'],
            [$this->aidrs],
            ['', 'http://www.example2.com/']
        );
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor with a wrong AuthnQueryService fails.
     */
    public function testMarshallingWithWrongAuthnQueryService(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('AuthnQueryService must be an instance of EndpointType');
        new AuthnAuthorityDescriptor(
            [$this->aqs, ''],
            ['protocol1', 'protocol2'],
            [$this->aidrs],
            ['http://www.example1.com/', 'http://www.example2.com/']
        );
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor with a wrong AssertionIDRequestService fails.
     */
    public function testMarshallingWithWrongAssertionIDRequestService(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('AssertionIDRequestServices must be an instance of EndpointType');
        new AuthnAuthorityDescriptor(
            [$this->aqs],
            ['protocol1', 'protocol2'],
            [$this->aidrs, ''],
            ['http://www.example1.com/', 'http://www.example2.com/']
        );
    }


    // test unmarshalling


    /**
     * Test creating an AuthnAuthorityDescriptor from XML.
     */
    public function testUnmarshalling(): void
    {
        $aad = AuthnAuthorityDescriptor::fromXML($this->document->documentElement);
        $this->assertCount(1, $aad->getAuthnQueryServices());
        $this->assertEquals($this->aqs->getBinding(), $aad->getAuthnQueryServices()[0]->getBinding());
        $this->assertEquals($this->aqs->getLocation(), $aad->getAuthnQueryServices()[0]->getLocation());
        $this->assertCount(1, $aad->getAssertionIDRequestServices());
        $this->assertEquals($this->aidrs->getBinding(), $aad->getAssertionIDRequestServices()[0]->getBinding());
        $this->assertEquals($this->aidrs->getLocation(), $aad->getAssertionIDRequestServices()[0]->getLocation());
        $this->assertEquals(['http://www.example1.com/', 'http://www.example2.com/'], $aad->getNameIDFormats());
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML fails if no AuthnQueryService was provided.
     */
    public function testUnmarshallingWithoutAuthnQueryService(): void
    {
        $aqs = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'AuthnQueryService');
        $this->document->documentElement->removeChild($aqs->item(0));
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing at least one AuthnQueryService in AuthnAuthorityDescriptor.');
        AuthnAuthorityDescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML fails if an empty NameIDFormat was provided.
     */
    public function testUnmarshallingWithEmptyNameIDFormat(): void
    {
        $nidf = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'NameIDFormat');
        $nidf->item(0)->textContent = '';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('NameIDFormat cannot be an empty string.');
        AuthnAuthorityDescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML without AssertionRequestIDService elements works.
     */
    public function testUnmarshallingWithoutAssertionIDRequestServices(): void
    {
        $aidrs = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'AssertionIDRequestService');
        $this->document->documentElement->removeChild($aidrs->item(0));
        AuthnAuthorityDescriptor::fromXML($this->document->documentElement);
        $this->assertTrue(true);
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML without NameIDFormat elements works.
     */
    public function testUnmarshallingWithoutNameIDFormats(): void
    {
        $nidf = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'NameIDFormat');
        $this->document->documentElement->removeChild($nidf->item(1));
        $this->document->documentElement->removeChild($nidf->item(0));
        AuthnAuthorityDescriptor::fromXML($this->document->documentElement);
        $this->assertTrue(true);
    }


    /**
     * Test that serialization works.
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(AuthnAuthorityDescriptor::fromXML($this->document->documentElement))))
        );
    }
}
