<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AuthzService;
use SimpleSAML\SAML2\XML\md\NameIDFormat;
use SimpleSAML\SAML2\XML\md\PDPDescriptor;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Tests for md:PDPDescriptor
 *
 * @covers \SimpleSAML\SAML2\XML\md\PDPDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMetadataDocument
 * @covers \SimpleSAML\SAML2\XML\md\AbstractSignedMdElement
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class PDPDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /** @var \SimpleSAML\SAML2\XML\md\AuthzService */
    protected AuthzService $authzService;

    /** @var \SimpleSAML\SAML2\XML\md\AssertionIDRequestService */
    protected AssertionIDRequestService $assertionIDRequestService;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = PDPDescriptor::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_PDPDescriptor.xml'
        );

        $this->authzService = new AuthzService(
            C::BINDING_SOAP,
            'https://IdentityProvider.com/SAML/AA/SOAP'
        );
        $this->assertionIDRequestService = new AssertionIDRequestService(
            C::BINDING_URI,
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
                new NameIDFormat(C::NAMEID_X509_SUBJECT_NAME),
                new NameIDFormat(C::NAMEID_PERSISTENT),
                new NameIDFormat(C::NAMEID_TRANSIENT),
            ]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
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
        $pdpd = PDPDescriptor::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($pdpd)
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
        $this->xmlRepresentation->documentElement->removeChild(
            $this->xmlRepresentation->documentElement->firstChild->nextSibling
        );

        $this->expectException(AssertionFailedException::class);

        $this->expectExceptionMessage('At least one md:AuthzService endpoint must be present.');
        PDPDescriptor::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a PDPDescriptor from XML works when no optional arguments are found.
     */
    public function testUnmarshallingWithoutOptionalArguments(): void
    {
        $mdns = C::NS_MD;
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
}
