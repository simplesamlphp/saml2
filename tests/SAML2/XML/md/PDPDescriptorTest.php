<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Tests for md:PDPDescriptor
 *
 * @covers \SimpleSAML\SAML2\XML\md\PDPDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMetadataDocument
 * @covers \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor
 * @package simplesamlphp/saml2
 */
final class PDPDescriptorTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;

    /** @var \SimpleSAML\SAML2\XML\md\AuthzService */
    protected $authzService;

    /** @var \SimpleSAML\SAML2\XML\md\AssertionIDRequestService */
    protected $assertionIDRequestService;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:PDPDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:md="{$mdns}">
  <md:AuthzService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
      Location="https://IdentityProvider.com/SAML/AA/SOAP"/>
  <md:AssertionIDRequestService Binding="urn:oasis:names:tc:SAML:2.0:bindings:URI"
      Location="https://IdentityProvider.com/SAML/AA/URI"/>
  <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName</md:NameIDFormat>
  <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:persistent</md:NameIDFormat>
  <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:transient</md:NameIDFormat>
</md:PDPDescriptor>
XML
        );

        $this->authzService = new AuthzService(
            'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
            'https://IdentityProvider.com/SAML/AA/SOAP'
        );
        $this->assertionIDRequestService = new AssertionIDRequestService(
            'urn:oasis:names:tc:SAML:2.0:bindings:URI',
            'https://IdentityProvider.com/SAML/AA/URI'
        );
    }


    // test marshalling


    /**
     * Test creating a PDPDescriptor object from scratch.
     */
    public function testMarshalling(): void
    {
        $pdpd = new PDPDescriptor(
            [$this->authzService],
            ["urn:oasis:names:tc:SAML:2.0:protocol"],
            [$this->assertionIDRequestService],
            [
                'urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            ]
        );

        $this->assertCount(1, $pdpd->getAuthzServiceEndpoints());
        $this->assertInstanceOf(AuthzService::class, $pdpd->getAuthzServiceEndpoints()[0]);
        $this->assertCount(1, $pdpd->getAssertionIDRequestServices());
        $this->assertInstanceOf(AssertionIDRequestService::class, $pdpd->getAssertionIDRequestServices()[0]);
        $this->assertCount(3, $pdpd->getNameIDFormats());
        $this->assertEquals(
            [
                'urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            ],
            $pdpd->getNameIDFormats()
        );

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($pdpd)
        );
    }


    /**
     * Test that creating a PDPDescriptor from scratch fails when an invalid AuthzService is passed.
     */
    public function testMarshallingWithWrongAuthzService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('All md:AuthzService endpoints must be an instance of AuthzService.');

        /** @psalm-suppress InvalidArgument */
        new PDPDescriptor(
            [$this->authzService, $this->assertionIDRequestService],
            ["urn:oasis:names:tc:SAML:2.0:protocol"]
        );
    }


    /**
     * Test that creating a PDPDescriptor from scratch fails when an invalid AssertionIDRequestService is passed.
     */
    public function testMarshallingWithWrongAssertionIDRequestService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'All md:AssertionIDRequestService endpoints must be an instance of AssertionIDRequestService.'
        );

        /** @psalm-suppress InvalidArgument */
        new PDPDescriptor(
            [$this->authzService],
            ["urn:oasis:names:tc:SAML:2.0:protocol"],
            [$this->assertionIDRequestService, $this->authzService]
        );
    }


    /**
     * Test that creating a PDPDescriptor from scratch fails when a non-string NameIDFormat is passed.
     */
    public function testMarshallingWithWrongNameIDFormat(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('All NameIDFormat must be a non-empty string.');

        /** @psalm-suppress InvalidScalarArgument */
        new PDPDescriptor(
            [$this->authzService],
            ["urn:oasis:names:tc:SAML:2.0:protocol"],
            [$this->assertionIDRequestService],
            ['format1', 10]
        );
    }


    /**
     * Test that creating a PDPDescriptor from scratch without any optional arguments works.
     */
    public function testMarshallingWithoutOptionalArguments(): void
    {
        $pdpd = new PDPDescriptor(
            [$this->authzService],
            ["urn:oasis:names:tc:SAML:2.0:protocol"]
        );
        $this->assertEmpty($pdpd->getAssertionIDRequestServices());
        $this->assertEmpty($pdpd->getNameIDFormats());
    }


    // test unmarshalling


    /**
     * Test creating a PDPDescriptor object from XML.
     */
    public function testUnmarshalling(): void
    {
        $pdpd = PDPDescriptor::fromXML($this->document->documentElement);
        $this->assertCount(1, $pdpd->getAuthzServiceEndpoints());
        $this->assertInstanceOf(AuthzService::class, $pdpd->getAuthzServiceEndpoints()[0]);
        $this->assertCount(1, $pdpd->getAssertionIDRequestServices());
        $this->assertInstanceOf(AssertionIDRequestService::class, $pdpd->getAssertionIDRequestServices()[0]);
        $this->assertCount(3, $pdpd->getNameIDFormats());
        $this->assertEquals(
            [
                'urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            ],
            $pdpd->getNameIDFormats()
        );
    }


    /**
     * Test that creating a PDPDescriptor from XML fails when there's no AuthzService endpoint.
     */
    public function testUnmarshallingWithoutAuthzServiceDescriptors(): void
    {
        /**
         * @psalm-suppress PossiblyNullArgument
         * @psalm-suppress PossiblyNullPropertyFetch
         */
        $this->document->documentElement->removeChild($this->document->documentElement->firstChild->nextSibling);

        $this->expectException(AssertionFailedException::class);

        $this->expectExceptionMessage('At least one md:AuthzService endpoint must be present.');
        PDPDescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a PDPDescriptor from XML works when no optional arguments are found.
     */
    public function testUnmarshallingWithoutOptionalArguments(): void
    {
        $mdns = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:PDPDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:md="{$mdns}">
  <md:AuthzService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
      Location="https://IdentityProvider.com/SAML/AA/SOAP"/>
</md:PDPDescriptor>
XML
        );
        $pdpd = PDPDescriptor::fromXML($document->documentElement);
        $this->assertEmpty($pdpd->getAssertionIDRequestServices());
        $this->assertEmpty($pdpd->getNameIDFormats());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(PDPDescriptor::fromXML($this->document->documentElement))))
        );
    }
}
