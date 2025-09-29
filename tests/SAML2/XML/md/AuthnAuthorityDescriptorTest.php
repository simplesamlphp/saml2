<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\AbstractMetadataDocument;
use SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor;
use SimpleSAML\SAML2\XML\md\AbstractRoleDescriptorType;
use SimpleSAML\SAML2\XML\md\AbstractSignedMdElement;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AuthnAuthorityDescriptor;
use SimpleSAML\SAML2\XML\md\AuthnQueryService;
use SimpleSAML\SAML2\XML\md\NameIDFormat;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(AuthnAuthorityDescriptor::class)]
#[CoversClass(AbstractRoleDescriptor::class)]
#[CoversClass(AbstractRoleDescriptorType::class)]
#[CoversClass(AbstractMetadataDocument::class)]
#[CoversClass(AbstractSignedMdElement::class)]
#[CoversClass(AbstractMdElement::class)]
final class AuthnAuthorityDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /** @var \SimpleSAML\SAML2\XML\md\AssertionIDRequestService */
    private static AssertionIDRequestService $aidrs;

    /** @var \SimpleSAML\SAML2\XML\md\AuthnQueryService */
    private static AuthnQueryService $aqs;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AuthnAuthorityDescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AuthnAuthorityDescriptor.xml',
        );

        self::$aqs = new AuthnQueryService(C::BINDING_HTTP_POST, 'http://www.example.com/aqs');
        self::$aidrs = new AssertionIDRequestService(C::BINDING_HTTP_POST, 'http://www.example.com/aidrs');
    }


    // test marshalling


    /**
     * Test creating an AuthnAuthorityDescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $aad = new AuthnAuthorityDescriptor(
            [self::$aqs],
            [C::NS_SAMLP, C::PROTOCOL],
            [self::$aidrs],
            [new NameIDFormat(C::NAMEID_PERSISTENT), new NameIDFormat(C::NAMEID_TRANSIENT)],
            'phpunit',
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($aad),
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
            [self::$aidrs],
            [new NameIDFormat(C::NAMEID_PERSISTENT), new NameIDFormat(C::NAMEID_TRANSIENT)],
        );
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor without optional elements works.
     */
    #[DoesNotPerformAssertions]
    public function testMarshallingWithoutOptionalElements(): void
    {
        new AuthnAuthorityDescriptor(
            [self::$aqs],
            [C::NS_SAMLP, C::PROTOCOL],
        );
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor with an empty NameIDFormat fails.
     */
    public function testMarshallWithEmptyNameIDFormat(): void
    {
        $this->expectException(ProtocolViolationException::class);
        new AuthnAuthorityDescriptor(
            [self::$aqs],
            [C::NS_SAMLP, C::PROTOCOL],
            [self::$aidrs],
            [new NameIDFormat(''), new NameIDFormat(C::NAMEID_TRANSIENT)],
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
            [self::$aqs, ''],
            [C::NS_SAMLP, C::PROTOCOL],
            [self::$aidrs],
            [new NameIDFormat(C::NAMEID_PERSISTENT), new NameIDFormat(C::NAMEID_TRANSIENT)],
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
            [self::$aqs],
            [C::NS_SAMLP, C::PROTOCOL],
            [self::$aidrs, ''],
            [new NameIDFormat(C::NAMEID_PERSISTENT), new NameIDFormat(C::NAMEID_TRANSIENT)],
        );
    }


    // test unmarshalling


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML fails if no AuthnQueryService was provided.
     */
    public function testUnmarshallingWithoutAuthnQueryService(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $aqs = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'AuthnQueryService');
        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($aqs->item(0));

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing at least one AuthnQueryService in AuthnAuthorityDescriptor.');

        AuthnAuthorityDescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML fails if an empty NameIDFormat was provided.
     */
    public function testUnmarshallingWithEmptyNameIDFormat(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $nidf = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'NameIDFormat');
        /** @psalm-suppress PossiblyNullPropertyAssignment */
        $nidf->item(0)->textContent = '';
        $this->expectException(ProtocolViolationException::class);

        AuthnAuthorityDescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML without AssertionRequestIDService elements works.
     */
    #[DoesNotPerformAssertions]
    public function testUnmarshallingWithoutAssertionIDRequestServices(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $aidrs = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'AssertionIDRequestService');
        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($aidrs->item(0));
        AuthnAuthorityDescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an AuthnAuthorityDescriptor from XML without NameIDFormat elements works.
     */
    #[DoesNotPerformAssertions]
    public function testUnmarshallingWithoutNameIDFormats(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $nidf = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'NameIDFormat');
        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($nidf->item(1));
        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($nidf->item(0));
        AuthnAuthorityDescriptor::fromXML($xmlRepresentation->documentElement);
    }
}
