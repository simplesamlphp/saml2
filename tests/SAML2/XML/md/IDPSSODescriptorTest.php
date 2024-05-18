<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\AbstractMetadataDocument;
use SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor;
use SimpleSAML\SAML2\XML\md\AbstractRoleDescriptorType;
use SimpleSAML\SAML2\XML\md\AbstractSignedMdElement;
use SimpleSAML\SAML2\XML\md\ArtifactResolutionService;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AttributeProfile;
use SimpleSAML\SAML2\XML\md\IDPSSODescriptor;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\SAML2\XML\md\ManageNameIDService;
use SimpleSAML\SAML2\XML\md\NameIDFormat;
use SimpleSAML\SAML2\XML\md\NameIDMappingService;
use SimpleSAML\SAML2\XML\md\SingleLogoutService;
use SimpleSAML\SAML2\XML\md\SingleSignOnService;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;

use function dirname;
use function strval;

/**
 * Tests for IDPSSODescriptor.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(IDPSSODescriptor::class)]
#[CoversClass(AbstractRoleDescriptor::class)]
#[CoversClass(AbstractRoleDescriptorType::class)]
#[CoversClass(AbstractMetadataDocument::class)]
#[CoversClass(AbstractSignedMdElement::class)]
#[CoversClass(AbstractMdElement::class)]
final class IDPSSODescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = IDPSSODescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_IDPSSODescriptor.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an IDPSSODescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $idpssod = new IDPSSODescriptor(
            singleSignOnService: [
                new SingleSignOnService(
                    C::BINDING_HTTP_REDIRECT,
                    'https://IdentityProvider.com/SAML/SSO/Browser',
                ),
                new SingleSignOnService(
                    C::BINDING_HTTP_POST,
                    'https://IdentityProvider.com/SAML/SSO/Browser',
                ),
            ],
            protocolSupportEnumeration: [C::NS_SAMLP],
            wantAuthnRequestsSigned: true,
            nameIDMappingService: [
                new NameIDMappingService(
                    C::BINDING_HTTP_REDIRECT,
                    'https://IdentityProvider.com/SAML/SSO/Browser',
                ),
                new NameIDMappingService(
                    C::BINDING_HTTP_POST,
                    'https://IdentityProvider.com/SAML/SSO/Browser',
                ),
            ],
            assertionIDRequestService: [
                new AssertionIDRequestService(
                    C::BINDING_HTTP_REDIRECT,
                    'https://IdentityProvider.com/SAML/SSO/Browser',
                ),
                new AssertionIDRequestService(
                    C::BINDING_HTTP_POST,
                    'https://IdentityProvider.com/SAML/SSO/Browser',
                ),
            ],
            attributeProfile: [
                new AttributeProfile('urn:attribute:profile1'),
                new AttributeProfile('urn:attribute:profile2'),
            ],
            attribute: [
                new Attribute(
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.6',
                    C::NAMEFORMAT_URI,
                    'eduPersonPrincipalName',
                ),
                new Attribute(
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.1',
                    C::NAMEFORMAT_URI,
                    'eduPersonAffiliation',
                    [
                        new AttributeValue('member'),
                        new AttributeValue('student'),
                        new AttributeValue('faculty'),
                        new AttributeValue('employee'),
                        new AttributeValue('staff'),
                    ],
                ),
            ],
            keyDescriptor: [
                new KeyDescriptor(
                    new KeyInfo(
                        [new KeyName('IdentityProvider.com SSO Key')]
                    ),
                    'signing',
                ),
            ],
            artifactResolutionService: [
                new ArtifactResolutionService(
                    0,
                    C::BINDING_SOAP,
                    'https://IdentityProvider.com/SAML/Artifact',
                    true,
                ),
            ],
            singleLogoutService: [
                new SingleLogoutService(
                    C::BINDING_SOAP,
                    'https://IdentityProvider.com/SAML/SLO/SOAP',
                ),
                new SingleLogoutService(
                    C::BINDING_HTTP_REDIRECT,
                    'https://IdentityProvider.com/SAML/SLO/Browser',
                    'https://IdentityProvider.com/SAML/SLO/Response',
                ),
            ],
            manageNameIDService: [
                new ManageNameIDService(
                    C::BINDING_HTTP_POST,
                    'https://IdentityProvider.com/SAML/SSO/Browser',
                ),
            ],
            nameIDFormat: [
                new NameIDFormat(C::NAMEID_X509_SUBJECT_NAME),
                new NameIDFormat(C::NAMEID_PERSISTENT),
                new NameIDFormat(C::NAMEID_TRANSIENT),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($idpssod),
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if no SingleSignOnService endpoints are provided.
     */
    public function testMarshallingWithEmptySingleSignOnService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('At least one SingleSignOnService must be specified.');
        new IDPSSODescriptor([], [C::NS_SAMLP]);
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if no protocol is passed.
     */
    public function testMarshallingWithoutProtocolSupportThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('At least one protocol must be supported by this md:IDPSSODescriptor.');

        new IDPSSODescriptor(
            [new SingleSignOnService(C::BINDING_HTTP_POST, C::LOCATION_A)],
            [],
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if an empty AttributeProfile is provided.
     */
    public function testMarshallingWithEmptyAttributeProfile(): void
    {
        $this->expectException(SchemaViolationException::class);
        new IDPSSODescriptor(
            singleSignOnService: [new SingleSignOnService(C::BINDING_HTTP_POST, C::LOCATION_A)],
            protocolSupportEnumeration: [C::NS_SAMLP],
            attributeProfile: [new AttributeProfile('profile1'), new AttributeProfile('')],
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch works if no optional arguments are provided.
     */
    public function testMarshallingWithoutOptionalArguments(): void
    {
        $idpssod = new IDPSSODescriptor(
            [
                new SingleSignOnService(C::BINDING_HTTP_POST, C::LOCATION_A),
                new SingleSignOnService(C::BINDING_HTTP_REDIRECT, C::LOCATION_B),
            ],
            [C::NS_SAMLP, C::PROTOCOL],
        );
        $this->assertNull($idpssod->wantAuthnRequestsSigned());
        $this->assertEquals([], $idpssod->getNameIDMappingService());
        $this->assertEquals([], $idpssod->getAssertionIDRequestService());
        $this->assertEquals([], $idpssod->getSupportedAttribute());
    }


    // test unmarshalling


    /**
     * Test that creating an IDPSSODescriptor from XML fails if no SingleSignOnService endpoint is provided.
     */
    public function testUnmarshallingWithoutSingleSignOnService(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $ssoServiceEps = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'SingleSignOnService');
        $xmlRepresentation->documentElement->removeChild($ssoServiceEps->item(1));
        $xmlRepresentation->documentElement->removeChild($ssoServiceEps->item(0));

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('At least one SingleSignOnService must be specified.');

        IDPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IDPSSODescriptor from XML fails if WantAuthnRequestsSigned is not boolean.
     */
    public function testUnmarshallingWithWrongWantAuthnRequestsSigned(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('WantAuthnRequestsSigned', 'not a boolean');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'WantAuthnRequestsSigned\' attribute of md:IDPSSODescriptor must be a boolean.',
        );

        IDPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IDPSSODescriptor from XML fails if an empty AttributeProfile is provided.
     */
    public function testUnmarshallingWithEmptyAttributeProfile(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $attrProfiles = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'AttributeProfile');
        $attrProfiles->item(0)->textContent = '';

        $this->expectException(SchemaViolationException::class);

        IDPSSODescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IDPSSODescriptor from XML works if no optional elements are provided.
     */
    public function testUnmarshallingWithoutOptionalArguments(): void
    {
        $mdns = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:IDPSSODescriptor xmlns:md="{$mdns}" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
  <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
      Location="https://IdentityProvider.com/SAML/SSO/Browser"/>
</md:IDPSSODescriptor>
XML
        );
        $idpssod = IDPSSODescriptor::fromXML($document->documentElement);
        $this->assertCount(1, $idpssod->getSingleSignOnService());
        $this->assertInstanceOf(SingleSignOnService::class, $idpssod->getSingleSignOnService()[0]);
        $this->assertNull($idpssod->wantAuthnRequestsSigned());
        $this->assertEquals([], $idpssod->getNameIDMappingService());
        $this->assertEquals([], $idpssod->getAssertionIDRequestService());
        $this->assertEquals([], $idpssod->getAttributeProfile());
        $this->assertEquals([], $idpssod->getSupportedAttribute());
    }
}
