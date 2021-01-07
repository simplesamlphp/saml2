<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\SignedElementTestTrait;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;
use SimpleSAML\XMLSecurity\XMLSecurityDSig;

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
    use SignedElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_IDPSSODescriptor.xml'
        );
        $this->testedClass = IDPSSODescriptor::class;
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
                    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                ),
                new SingleSignOnService(
                    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                )
            ],
            ['urn:oasis:names:tc:SAML:2.0:protocol'],
            true,
            [
                new NameIDMappingService(
                    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                ),
                new NameIDMappingService(
                    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                )
            ],
            [
                new AssertionIDRequestService(
                    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                ),
                new AssertionIDRequestService(
                    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                )
            ],
            ['urn:attribute:profile1', 'urn:attribute:profile2'],
            [
                new Attribute(
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.6',
                    'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                    'eduPersonPrincipalName'
                ),
                new Attribute(
                    'urn:oid:1.3.6.1.4.1.5923.1.1.1.1',
                    'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
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
                    'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                    'https://IdentityProvider.com/SAML/Artifact',
                    true
                )
            ],
            [
                new SingleLogoutService(
                    'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                    'https://IdentityProvider.com/SAML/SLO/SOAP'
                ),
                new SingleLogoutService(
                    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'https://IdentityProvider.com/SAML/SLO/Browser',
                    'https://IdentityProvider.com/SAML/SLO/Response'
                )
            ],
            [
                new ManageNameIDService(
                    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'https://IdentityProvider.com/SAML/SSO/Browser'
                )
            ],
            [
                'urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'
            ]
        );

        $this->assertCount(2, $idpssod->getSingleSignOnServices());
        $this->assertInstanceOf(SingleSignOnService::class, $idpssod->getSingleSignOnServices()[0]);
        $this->assertInstanceOf(SingleSignOnService::class, $idpssod->getSingleSignOnServices()[1]);
        $this->assertTrue($idpssod->wantAuthnRequestsSigned());
        $this->assertCount(2, $idpssod->getNameIDMappingServices());
        $this->assertInstanceOf(NameIDMappingService::class, $idpssod->getNameIDMappingServices()[0]);
        $this->assertInstanceOf(NameIDMappingService::class, $idpssod->getNameIDMappingServices()[1]);
        $this->assertCount(2, $idpssod->getAssertionIDRequestServices());
        $this->assertInstanceOf(AssertionIDRequestService::class, $idpssod->getAssertionIDRequestServices()[0]);
        $this->assertInstanceOf(AssertionIDRequestService::class, $idpssod->getAssertionIDRequestServices()[1]);
        $this->assertEquals(
            ['urn:attribute:profile1', 'urn:attribute:profile2'],
            $idpssod->getAttributeProfiles()
        );
        $this->assertCount(2, $idpssod->getSupportedAttributes());
        $this->assertInstanceOf(Attribute::class, $idpssod->getSupportedAttributes()[0]);
        $this->assertInstanceOf(Attribute::class, $idpssod->getSupportedAttributes()[1]);
        $this->assertCount(1, $idpssod->getArtifactResolutionServices());
        $this->assertInstanceOf(ArtifactResolutionService::class, $idpssod->getArtifactResolutionServices()[0]);
        $this->assertCount(1, $idpssod->getManageNameIDServices());
        $this->assertInstanceOf(ManageNameIDService::class, $idpssod->getManageNameIDServices()[0]);
        $this->assertCount(2, $idpssod->getSingleLogoutServices());
        $this->assertInstanceOf(SingleLogoutService::class, $idpssod->getSingleLogoutServices()[0]);
        $this->assertInstanceOf(SingleLogoutService::class, $idpssod->getSingleLogoutServices()[1]);
        $this->assertEquals(
            [
                'urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:transient'
            ],
            $idpssod->getNameIDFormats()
        );

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
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
        new IDPSSODescriptor([], ['protocol1']);
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
            ['protocol1']
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
            ['protocol1'],
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
            ['protocol1'],
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
        $this->expectExceptionMessage('All md:AttributeProfile elements must be a URI, not an empty string.');
        new IDPSSODescriptor(
            [new SingleSignOnService('binding1', 'location1')],
            ['protocol1'],
            null,
            [],
            [],
            ['profile1', '']
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
            ['protocol1'],
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
            ['protocol1', 'protocol2']
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
        $idpssod = IDPSSODescriptor::fromXML($this->document->documentElement);
        $this->assertCount(2, $idpssod->getSingleSignOnServices());
        $this->assertInstanceOf(SingleSignOnService::class, $idpssod->getSingleSignOnServices()[0]);
        $this->assertInstanceOf(SingleSignOnService::class, $idpssod->getSingleSignOnServices()[1]);
        $this->assertTrue($idpssod->wantAuthnRequestsSigned());
        $this->assertCount(2, $idpssod->getNameIDMappingServices());
        $this->assertInstanceOf(NameIDMappingService::class, $idpssod->getNameIDMappingServices()[0]);
        $this->assertInstanceOf(NameIDMappingService::class, $idpssod->getNameIDMappingServices()[1]);
        $this->assertCount(2, $idpssod->getAssertionIDRequestServices());
        $this->assertInstanceOf(AssertionIDRequestService::class, $idpssod->getAssertionIDRequestServices()[0]);
        $this->assertInstanceOf(AssertionIDRequestService::class, $idpssod->getAssertionIDRequestServices()[1]);
        $this->assertEquals(
            ['urn:attribute:profile1', 'urn:attribute:profile2'],
            $idpssod->getAttributeProfiles()
        );
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
        $ssoServiceEps = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'SingleSignOnService');
        /** @psalm-suppress PossiblyNullArgument */
        $this->document->documentElement->removeChild($ssoServiceEps->item(1));
        /** @psalm-suppress PossiblyNullArgument */
        $this->document->documentElement->removeChild($ssoServiceEps->item(0));
        IDPSSODescriptor::fromXML($this->document->documentElement);
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
        $this->document->documentElement->setAttribute('WantAuthnRequestsSigned', 'not a boolean');
        IDPSSODescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an IDPSSODescriptor from XML fails if an empty AttributeProfile is provided.
     */
    public function testUnmarshallingWithEmptyAttributeProfile(): void
    {
        $attrProfiles = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'AttributeProfile');
        /** @psalm-suppress PossiblyNullPropertyAssignment */
        $attrProfiles->item(0)->textContent = '';
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('All md:AttributeProfile elements must be a URI, not an empty string.');
        IDPSSODescriptor::fromXML($this->document->documentElement);
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


    /**
     * Test serialization / unserialization.
     *
     */
    public function testSerialize(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(IDPSSODescriptor::fromXML($this->document->documentElement))))
        );
    }
}
