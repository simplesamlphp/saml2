<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AuthnAuthorityDescriptor;
use SimpleSAML\SAML2\XML\md\AuthnQueryService;
use SimpleSAML\SAML2\XML\md\NameIDFormat;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\SignedElementTestTrait;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\Utils as XMLUtils;

use function dirname;
use function strval;

/**
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @covers \SimpleSAML\SAML2\XML\md\AbstractSignedMdElement
 * @covers \SimpleSAML\SAML2\XML\md\AuthnAuthorityDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMetadataDocument
 * @covers \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor
 * @package simplesamlphp/saml2
 */
final class AuthnAuthorityDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /** @var \SimpleSAML\SAML2\XML\md\AssertionIDRequestService */
    protected AssertionIDRequestService $aidrs;

    /** @var \SimpleSAML\SAML2\XML\md\AuthnQueryService */
    protected AuthnQueryService $aqs;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = AuthnAuthorityDescriptor::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AuthnAuthorityDescriptor.xml'
        );

        $this->aqs = new AuthnQueryService(C::BINDING_HTTP_POST, 'http://www.example.com/aqs');
        $this->aidrs = new AssertionIDRequestService(C::BINDING_HTTP_POST, 'http://www.example.com/aidrs');
    }


    // test marshalling


    /**
     * Test creating an AuthnAuthorityDescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $aad = new AuthnAuthorityDescriptor(
            [$this->aqs],
            [C::NS_SAMLP, C::PROTOCOL],
            [$this->aidrs],
            [new NameIDFormat(C::NAMEID_PERSISTENT), new NameIDFormat(C::NAMEID_TRANSIENT)]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($aad)
        );
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor without AuthnQueryService elements fails.
     */
    public function testMarshallingWithoutAuthnQueryServices(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing at least one AuthnQueryService in AuthnAuthorityDescriptor.');
        new AuthnAuthorityDescriptor(
            [],
            [C::NS_SAMLP, C::PROTOCOL],
            [$this->aidrs],
            [new NameIDFormat(C::NAMEID_PERSISTENT), new NameIDFormat(C::NAMEID_TRANSIENT)]
        );
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor without optional elements works.
     */
    public function testMarshallingWithoutOptionalElements(): void
    {
        new AuthnAuthorityDescriptor(
            [$this->aqs],
            [C::NS_SAMLP, C::PROTOCOL]
        );

        $this->assertTrue(true);
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor with an empty NameIDFormat fails.
     */
    public function testMarshallWithEmptyNameIDFormat(): void
    {
        $this->expectException(SchemaViolationException::class);
        new AuthnAuthorityDescriptor(
            [$this->aqs],
            [C::NS_SAMLP, C::PROTOCOL],
            [$this->aidrs],
            [new NameIDFormat(''), new NameIDFormat(C::NAMEID_TRANSIENT)]
        );
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor with a wrong AuthnQueryService fails.
     */
    public function testMarshallingWithWrongAuthnQueryService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('AuthnQueryService must be an instance of EndpointType');
        new AuthnAuthorityDescriptor(
            [$this->aqs, ''],
            [C::NS_SAMLP, C::PROTOCOL],
            [$this->aidrs],
            [new NameIDFormat(C::NAMEID_PERSISTENT), new NameIDFormat(C::NAMEID_TRANSIENT)]
        );
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor with a wrong AssertionIDRequestService fails.
     */
    public function testMarshallingWithWrongAssertionIDRequestService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('AssertionIDRequestServices must be an instance of EndpointType');
        new AuthnAuthorityDescriptor(
            [$this->aqs],
            [C::NS_SAMLP, C::PROTOCOL],
            [$this->aidrs, ''],
            [new NameIDFormat(C::NAMEID_PERSISTENT), new NameIDFormat(C::NAMEID_TRANSIENT)]
        );
    }


    // test unmarshalling


    /**
     * Test creating an AuthnAuthorityDescriptor from XML.
     */
    public function testUnmarshalling(): void
    {
        $aad = AuthnAuthorityDescriptor::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($aad)
        );
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML fails if no AuthnQueryService was provided.
     */
    public function testUnmarshallingWithoutAuthnQueryService(): void
    {
        $aqs = $this->xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'AuthnQueryService');
        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($aqs->item(0));
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing at least one AuthnQueryService in AuthnAuthorityDescriptor.');
        AuthnAuthorityDescriptor::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML fails if an empty NameIDFormat was provided.
     */
    public function testUnmarshallingWithEmptyNameIDFormat(): void
    {
        $nidf = $this->xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'NameIDFormat');
        /** @psalm-suppress PossiblyNullPropertyAssignment */
        $nidf->item(0)->textContent = '';
        $this->expectException(SchemaViolationException::class);
        AuthnAuthorityDescriptor::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML without AssertionRequestIDService elements works.
     */
    public function testUnmarshallingWithoutAssertionIDRequestServices(): void
    {
        $aidrs = $this->xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'AssertionIDRequestService');
        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($aidrs->item(0));
        AuthnAuthorityDescriptor::fromXML($this->xmlRepresentation->documentElement);
        $this->assertTrue(true);
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML without NameIDFormat elements works.
     */
    public function testUnmarshallingWithoutNameIDFormats(): void
    {
        $nidf = $this->xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'NameIDFormat');
        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($nidf->item(1));
        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($nidf->item(0));
        AuthnAuthorityDescriptor::fromXML($this->xmlRepresentation->documentElement);
        $this->assertTrue(true);
    }
}
