<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AttributeProfile;
use SimpleSAML\SAML2\XML\md\ArtifactResolutionService;
use SimpleSAML\SAML2\XML\md\IDPSSODescriptor;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\SAML2\XML\md\ManageNameIDService;
use SimpleSAML\SAML2\XML\md\NameIDMappingService;
use SimpleSAML\SAML2\XML\md\NameIDFormat;
use SimpleSAML\SAML2\XML\md\SingleLogoutService;
use SimpleSAML\SAML2\XML\md\SingleSignOnService;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\Test\SAML2\SignedElementTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;
use SimpleSAML\XMLSecurity\XMLSecurityDSig;

use function dirname;
use function strval;

/**
 * Tests for IDPSSODescriptor.
 *
 * @covers \SimpleSAML\SAML2\XML\md\IDPSSODescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMetadataDocument
 * @covers \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractSSODescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class IDPSSODescriptorTest extends TestCase
{
    use SerializableXMLTestTrait;
    use SignedElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = IDPSSODescriptor::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_IDPSSODescriptor.xml'
        );
    }


    // test marshalling


    /**
     * Test creating an IDPSSODescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $idpssod = new IDPSSODescriptor(
            [
                new SingleSignOnService(
                    Constants::BINDING_HTTP_REDIRECT,
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                ),
                new SingleSignOnService(
                    Constants::BINDING_HTTP_POST,
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                )
            ],
            [Constants::NS_SAMLP],
            true,
            [
                new NameIDMappingService(
                    Constants::BINDING_HTTP_REDIRECT,
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                ),
                new NameIDMappingService(
                    Constants::BINDING_HTTP_POST,
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                )
            ],
            [
                new AssertionIDRequestService(
                    Constants::BINDING_HTTP_REDIRECT,
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                ),
                new AssertionIDRequestService(
                    Constants::BINDING_HTTP_POST,
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                )
            ],
            [new AttributeProfile('urn:attribute:profile1'), new AttributeProfile('urn:attribute:profile2')],
            [
                new Attribute(
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.6',
                    Constants::NAMEFORMAT_URI,
                    'eduPersonPrincipalName'
                ),
                new Attribute(
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.1',
                    Constants::NAMEFORMAT_URI,
                    'eduPersonAffiliation',
                    [
                        new AttributeValue('member'),
                        new AttributeValue('student'),
                        new AttributeValue('faculty'),
                        new AttributeValue('employee'),
                        new AttributeValue('staff'),
                    ]
                )
            ],
            null,
            null,
            null,
            null,
            null,
            [
                new KeyDescriptor(
                    new KeyInfo(
                        [new KeyName('IdentityProvider.com SSO Key')]
                    ),
                    'signing'
                )
            ],
            null,
            [],
            [
                new ArtifactResolutionService(
                    0,
                    Constants::BINDING_SOAP,
                    'https://IdentityProvider.com/SAML/Artifact',
                    true
                )
            ],
            [
                new SingleLogoutService(
                    Constants::BINDING_SOAP,
                    'https://IdentityProvider.com/SAML/SLO/SOAP'
                ),
                new SingleLogoutService(
                    Constants::BINDING_HTTP_REDIRECT,
                    'https://IdentityProvider.com/SAML/SLO/Browser',
                    'https://IdentityProvider.com/SAML/SLO/Response'
                )
            ],
            [
                new ManageNameIDService(
                    Constants::BINDING_HTTP_POST,
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                )
            ],
            [
                new NameIDFormat(Constants::NAMEID_X509_SUBJECT_NAME),
                new NameIDFormat(Constants::NAMEID_PERSISTENT),
                new NameIDFormat(Constants::NAMEID_TRANSIENT)
            ]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($idpssod)
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if no SingleSignOnService endpoints are provided.
     */
    public function testMarshallingWithEmptySingleSignOnService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('At least one SingleSignOnService must be specified.');
        new IDPSSODescriptor([], [Constants::NS_SAMLP]);
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if SingleSignOnService endpoints passed have the
     * wrong type.
     */
    public function testMarshallingWithWrongSingleSignOnService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'All md:SingleSignOnService endpoints must be an instance of SingleSignOnService.'
        );

        /** @psalm-suppress InvalidArgument */
        new IDPSSODescriptor(
            [new AssertionIDRequestService('binding1', 'location1')],
            [Constants::NS_SAMLP]
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if the SAML 2.0 protocol
     * is not on of the supported protocols.
     */
    public function testMarshallingWithoutProtocolSupportThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('At least SAML 2.0 must be one of supported protocols.');

        /** @psalm-suppress InvalidArgument */
        new IDPSSODescriptor(
            [new AssertionIDRequestService('binding1', 'location1')],
            ['urn:saml:3.9']
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if NameIDMappingService endpoints passed have the
     * wrong type.
     */
    public function testMarshallingWithWrongNameIDMappingService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'All md:NameIDMappingService endpoints must be an instance of NameIDMappingService.'
        );

        /** @psalm-suppress InvalidArgument */
        new IDPSSODescriptor(
            [new SingleSignOnService('binding1', 'location1')],
            [Constants::NS_SAMLP],
            null,
            [new SingleSignOnService('binding1', 'location1')]
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if AssertionIDRequestService endpoints passed have the
     * wrong type.
     */
    public function testMarshallingWithWrongAssertionIDRequestService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'All md:AssertionIDRequestService endpoints must be an instance of AssertionIDRequestService.'
        );

        /** @psalm-suppress InvalidArgument */
        new IDPSSODescriptor(
            [new SingleSignOnService('binding1', 'location1')],
            [Constants::NS_SAMLP],
            null,
            [],
            [new SingleSignOnService('binding1', 'location1')]
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if an empty AttributeProfile is provided.
     */
    public function testMarshallingWithEmptyAttributeProfile(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('AttributeProfile cannot be empty');
        new IDPSSODescriptor(
            [new SingleSignOnService('binding1', 'location1')],
            [Constants::NS_SAMLP],
            null,
            [],
            [],
            [new AttributeProfile('profile1'), new AttributeProfile('')]
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch fails if attributes passed have the wrong type.
     */
    public function testMarshallingWithWrongAttributes(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('All md:Attribute elements must be an instance of Attribute.');

        /** @psalm-suppress InvalidArgument */
        new IDPSSODescriptor(
            [new SingleSignOnService('binding1', 'location1')],
            [Constants::NS_SAMLP],
            null,
            [],
            [],
            [],
            [new SingleSignOnService('binding1', 'location1')]
        );
    }


    /**
     * Test that creating an IDPSSODescriptor from scratch works if no optional arguments are provided.
     */
    public function testMarshallingWithoutOptionalArguments(): void
    {
        $idpssod = new IDPSSODescriptor(
            [
                new SingleSignOnService('binding1', 'location1'),
                new SingleSignOnService('binding2', 'location2')
            ],
            [Constants::NS_SAMLP, 'protocol2']
        );
        $this->assertNull($idpssod->wantAuthnRequestsSigned());
        $this->assertEquals([], $idpssod->getNameIDMappingServices());
        $this->assertEquals([], $idpssod->getAssertionIDRequestServices());
        $this->assertEquals([], $idpssod->getSupportedAttributes());
    }


    // test unmarshalling


    /**
     * Test creating an IDPSSODescriptor from XML.
     */
    public function testUnmarshalling(): void
    {
        $idpssod = IDPSSODescriptor::fromXML($this->xmlRepresentation->documentElement);
        $this->assertCount(2, $idpssod->getSingleSignOnServices());
        $this->assertInstanceOf(SingleSignOnService::class, $idpssod->getSingleSignOnServices()[0]);
        $this->assertInstanceOf(SingleSignOnService::class, $idpssod->getSingleSignOnServices()[1]);
        $this->assertCount(2, $idpssod->getSingleLogoutServices());
        $this->assertInstanceOf(SingleLogoutService::class, $idpssod->getSingleLogoutServices()[0]);
        $this->assertInstanceOf(SingleLogoutService::class, $idpssod->getSingleLogoutServices()[1]);
        $this->assertTrue($idpssod->wantAuthnRequestsSigned());
        $this->assertCount(2, $idpssod->getNameIDMappingServices());
        $this->assertInstanceOf(NameIDMappingService::class, $idpssod->getNameIDMappingServices()[0]);
        $this->assertInstanceOf(NameIDMappingService::class, $idpssod->getNameIDMappingServices()[1]);
        $this->assertCount(3, $idpssod->getNameIDFormats());
        $this->assertEquals(Constants::NAMEID_X509_SUBJECT_NAME, $idpssod->getNameIDFormats()[0]->getContent());
        $this->assertEquals(Constants::NAMEID_PERSISTENT, $idpssod->getNameIDFormats()[1]->getContent());
        $this->assertEquals(Constants::NAMEID_TRANSIENT, $idpssod->getNameIDFormats()[2]->getContent());
        $this->assertCount(2, $idpssod->getAssertionIDRequestServices());
        $this->assertInstanceOf(AssertionIDRequestService::class, $idpssod->getAssertionIDRequestServices()[0]);
        $this->assertInstanceOf(AssertionIDRequestService::class, $idpssod->getAssertionIDRequestServices()[1]);

        $attributeProfiles = $idpssod->getAttributeProfiles();
        $this->assertCount(2, $attributeProfiles);
        $this->assertEquals('urn:attribute:profile1', $attributeProfiles[0]->getContent());
        $this->assertEquals('urn:attribute:profile2', $attributeProfiles[1]->getContent());

        $this->assertCount(2, $idpssod->getSupportedAttributes());
        $this->assertInstanceOf(Attribute::class, $idpssod->getSupportedAttributes()[0]);
        $this->assertInstanceOf(Attribute::class, $idpssod->getSupportedAttributes()[1]);
        $this->assertCount(1, $idpssod->getArtifactResolutionServices());
        $this->assertInstanceOf(ArtifactResolutionService::class, $idpssod->getArtifactResolutionServices()[0]);
        $this->assertCount(1, $idpssod->getManageNameIDServices());
        $this->assertInstanceOf(ManageNameIDService::class, $idpssod->getManageNameIDServices()[0]);
    }


    /**
     * Test that creating an IDPSSODescriptor from XML fails if no SingleSignOnService endpoint is provided.
     */
    public function testUnmarshallingWithoutSingleSignOnService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('At least one SingleSignOnService must be specified.');
        $ssoServiceEps = $this->xmlRepresentation->getElementsByTagNameNS(Constants::NS_MD, 'SingleSignOnService');
        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($ssoServiceEps->item(1));
        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($ssoServiceEps->item(0));
        IDPSSODescriptor::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IDPSSODescriptor from XML fails if WantAuthnRequestsSigned is not boolean.
     */
    public function testUnmarshallingWithWrongWantAuthnRequestsSigned(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'WantAuthnRequestsSigned\' attribute of md:IDPSSODescriptor must be boolean.'
        );
        $this->xmlRepresentation->documentElement->setAttribute('WantAuthnRequestsSigned', 'not a boolean');
        IDPSSODescriptor::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IDPSSODescriptor from XML fails if an empty AttributeProfile is provided.
     */
    public function testUnmarshallingWithEmptyAttributeProfile(): void
    {
        $attrProfiles = $this->xmlRepresentation->getElementsByTagNameNS(Constants::NS_MD, 'AttributeProfile');
        /** @psalm-suppress PossiblyNullPropertyAssignment */
        $attrProfiles->item(0)->textContent = '';
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('AttributeProfile cannot be empty');
        IDPSSODescriptor::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IDPSSODescriptor from XML works if no optional elements are provided.
     */
    public function testUnmarshallingWithoutOptionalArguments(): void
    {
        $mdns = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:IDPSSODescriptor xmlns:md="{$mdns}" protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
  <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
      Location="https://IdentityProvider.com/SAML/SSO/Browser"/>
</md:IDPSSODescriptor>
XML
        );
        $idpssod = IDPSSODescriptor::fromXML($document->documentElement);
        $this->assertCount(1, $idpssod->getSingleSignOnServices());
        $this->assertInstanceOf(SingleSignOnService::class, $idpssod->getSingleSignOnServices()[0]);
        $this->assertNull($idpssod->wantAuthnRequestsSigned());
        $this->assertEquals([], $idpssod->getNameIDMappingServices());
        $this->assertEquals([], $idpssod->getAssertionIDRequestServices());
        $this->assertEquals([], $idpssod->getAttributeProfiles());
        $this->assertEquals([], $idpssod->getSupportedAttributes());
    }
}
