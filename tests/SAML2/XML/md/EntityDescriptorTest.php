<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\md\EntityDescriptorTest
 */
class EntityDescriptorTest extends TestCase
{
    protected $document;


    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:EntityDescriptor xmlns:md="{$mdns}" entityID="urn:example:entity" ID="_5A3CHB081" validUntil="2020-02-05T09:39:25Z" cacheDuration="P2Y6M5DT12H35M30S">
  <md:IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://engine.test.example.edu/authentication/idp/single-sign-on"/>
  </md:IDPSSODescriptor>
  <md:AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    <md:AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP" Location="https://idp.example.org/AttributeService" />
  </md:AttributeAuthorityDescriptor>
  <md:AuthnAuthorityDescriptor protocolSupportEnumeration="protocol1">
    <md:AuthnQueryService Binding="uri:binding:aqs" Location="http://www.example.com/aqs" />
  </md:AuthnAuthorityDescriptor>
  <md:PDPDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    <md:AuthzService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP" Location="https://IdentityProvider.com/SAML/AA/SOAP"/>
  </md:PDPDescriptor>
  <md:Organization>
    <md:OrganizationName xml:lang="en">orgNameTest (en)</md:OrganizationName>
    <md:OrganizationDisplayName xml:lang="en">orgDispNameTest (en)</md:OrganizationDisplayName>
    <md:OrganizationURL xml:lang="en">orgURL (en)</md:OrganizationURL>
  </md:Organization>
  <md:ContactPerson contactType="support">
    <md:EmailAddress>mailto:help@example.edu</md:EmailAddress>
  </md:ContactPerson>
  <md:ContactPerson contactType="technical">
    <md:EmailAddress>mailto:root@example.edu</md:EmailAddress>
  </md:ContactPerson>
  <md:ContactPerson contactType="administrative">
    <md:EmailAddress>mailto:info@example.edu</md:EmailAddress>
  </md:ContactPerson>
  <md:AdditionalMetadataLocation namespace="somemd">https://example.edu/some/metadata.xml</md:AdditionalMetadataLocation>
  <md:AdditionalMetadataLocation namespace="mymd">https://example.edu/more/metadata.xml</md:AdditionalMetadataLocation>
</md:EntityDescriptor>
XML
        );
    }


    // test marshalling


    /**
     * Test creating an EntityDescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $entityid = "urn:example:entity";
        $id = "_5A3CHB081";
        $now = 1580895565;
        $duration = "P2Y6M5DT12H35M30S";
        $idpssod = new IDPSSODescriptor(
            [
                new SingleSignOnService(
                    'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'https://engine.test.example.edu/authentication/idp/single-sign-on'
                )
            ],
            ['urn:oasis:names:tc:SAML:2.0:protocol']
        );
        $attrad = new AttributeAuthorityDescriptor(
            [
                new AttributeService(
                    'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                    'https://idp.example.org/AttributeService'
                )
            ],
            ['urn:oasis:names:tc:SAML:2.0:protocol']
        );
        $authnad = new AuthnAuthorityDescriptor(
            [
                new AuthnQueryService(
                    'uri:binding:aqs',
                    'http://www.example.com/aqs'
                )
            ],
            ['protocol1']
        );
        $pdpd = new PDPDescriptor(
            [
                new AuthzService(
                    'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                    'https://IdentityProvider.com/SAML/AA/SOAP'
                )
            ],
            ['urn:oasis:names:tc:SAML:2.0:protocol']
        );
        $org = new Organization(
            [new OrganizationName('en', 'orgNameTest (en)')],
            [new OrganizationDisplayName('en', 'orgDispNameTest (en)')],
            ['en' => 'orgURL (en)']
        );
        $contacts = [
            new ContactPerson('support', null, null, null, null, ['help@example.edu']),
            new ContactPerson('technical', null, null, null, null, ['root@example.edu']),
            new ContactPerson('administrative', null, null, null, null, ['info@example.edu']),
        ];
        $mdloc = [
            new AdditionalMetadataLocation('somemd', 'https://example.edu/some/metadata.xml'),
            new AdditionalMetadataLocation('mymd', 'https://example.edu/more/metadata.xml'),
        ];

        $ed = new EntityDescriptor(
            $entityid,
            $id,
            $now,
            $duration,
            null,
            [
                $idpssod,
                $attrad,
                $authnad,
                $pdpd,
            ],
            null,
            $org,
            $contacts,
            $mdloc
        );
        $this->assertEquals($entityid, $ed->getEntityID());
        $this->assertEquals($id, $ed->getID());
        $this->assertEquals($now, $ed->getValidUntil());
        $this->assertEquals($duration, $ed->getCacheDuration());
        $this->assertCount(4, $ed->getRoleDescriptors());
        $this->assertInstanceOf(IDPSSODescriptor::class, $ed->getRoleDescriptors()[0]);
        $this->assertInstanceOf(AttributeAuthorityDescriptor::class, $ed->getRoleDescriptors()[1]);
        $this->assertInstanceOf(AuthnAuthorityDescriptor::class, $ed->getRoleDescriptors()[2]);
        $this->assertInstanceOf(PDPDescriptor::class, $ed->getRoleDescriptors()[3]);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($ed)
        );
    }


    /**
     * Test that creating an EntityDescriptor from scratch without any descriptors fails.
     */
    public function testMarshallingWithoutDescriptors(): void
    {
        $this->expectExceptionMessage(
            'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.'
        );
        new EntityDescriptor('entityID');
    }


    // test unmarshalling


    /**
     * Test creating an EntityDescriptor from XML.
     */
    public function testUnmarshalling(): void
    {
        $pdpd = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'PDPDescriptor')->item(0);
        $customd = $this->document->createElementNS(Constants::NS_MD, 'md:CustomRoleDescriptor');
        $customd->setAttribute('protocolSupportEnumeration', 'urn:oasis:names:tc:SAML:2.0:protocol');
        $newline = new \DOMText("\n  ");
        $pdpd->parentNode->insertBefore($customd, $pdpd->nextSibling);
        $pdpd->parentNode->insertBefore($newline, $customd);
        $entityDescriptor = EntityDescriptor::fromXML($this->document->documentElement);

        $this->assertInstanceOf(EntityDescriptor::class, $entityDescriptor);
        $this->assertEquals('urn:example:entity', $entityDescriptor->getEntityID());
        $this->assertEquals('_5A3CHB081', $entityDescriptor->getID());
        $this->assertEquals(1580895565, $entityDescriptor->getValidUntil());
        $this->assertEquals('P2Y6M5DT12H35M30S', $entityDescriptor->getCacheDuration());

        $roleDescriptors = $entityDescriptor->getRoleDescriptors();
        $this->assertCount(5, $roleDescriptors);
        $this->assertInstanceOf(IDPSSODescriptor::class, $roleDescriptors[0]);
        $this->assertInstanceOf(AttributeAuthorityDescriptor::class, $roleDescriptors[1]);
        $this->assertInstanceOf(AuthnAuthorityDescriptor::class, $roleDescriptors[2]);
        $this->assertInstanceOf(PDPDescriptor::class, $roleDescriptors[3]);
        $this->assertInstanceOf(UnknownRoleDescriptor::class, $roleDescriptors[4]);

        $this->assertInstanceOf(Organization::class, $entityDescriptor->getOrganization());

        $this->assertCount(3, $entityDescriptor->getContactPersons());
        $this->assertInstanceOf(ContactPerson::class, $entityDescriptor->getContactPersons()[0]);
        $this->assertInstanceOf(ContactPerson::class, $entityDescriptor->getContactPersons()[1]);
        $this->assertInstanceOf(ContactPerson::class, $entityDescriptor->getContactPersons()[2]);

        $this->assertCount(2, $entityDescriptor->getAdditionalMetadataLocations());
        $this->assertInstanceOf(
            AdditionalMetadataLocation::class,
            $entityDescriptor->getAdditionalMetadataLocations()[0]
        );
        $this->assertInstanceOf(
            AdditionalMetadataLocation::class,
            $entityDescriptor->getAdditionalMetadataLocations()[1]
        );

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($entityDescriptor)
        );
    }

    /**
     * Test that creating an EntityDescriptor from XML fails if no entityID is provided.
     */
    public function testUnmarshallingWithoutEntityId(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor affiliationOwnerID="asdf">
        <AffiliateMember>test</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing \'entityID\' attribute from md:EntityDescriptor.');
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an EntityDescriptor from XML fails if no RoleDescriptors are found.
     */
    public function testUnmarshallingWithoutDescriptors(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata"></EntityDescriptor>'
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.'
        );
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an EntityDescriptor from XML fails with an invalid validUntil attribute.
     */
    public function testUnmarshallingWithInvalidValidUntil(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor validUntil="asdf" entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor affiliationOwnerID="asd">
        <AffiliateMember>test</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid SAML2 timestamp passed to xsDateTimeToTimestamp: asdf');
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an EntityDescriptor from XML works when an AffiliationDescriptor is used.
     */
    public function testUnmarshallingWithAffiliationDescriptor(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata" validUntil="2010-02-01T12:34:56Z">
    <AffiliationDescriptor affiliationOwnerID="asdf" ID="theAffiliationDescriptorID" validUntil="2010-02-01T12:34:56Z" cacheDuration="PT9000S" >
        <AffiliateMember>test</AffiliateMember>
        <AffiliateMember>test2</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $entityDescriptor = EntityDescriptor::fromXML($document->documentElement);
        $this->assertInstanceOf(EntityDescriptor::class, $entityDescriptor);
        $this->assertEquals([], $entityDescriptor->getRoleDescriptors());
        $this->assertInstanceOf(AffiliationDescriptor::class, $entityDescriptor->getAffiliationDescriptor());
    }


    /**
     * Test that creating an EntityDescriptor from XML fails when multiple AffiliationDescriptors are found.
     */
    public function testUnmarshallingWithSeveralAffiliationDescriptors(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata" validUntil="2010-02-01T12:34:56Z">
    <AffiliationDescriptor affiliationOwnerID="asdf" ID="theAffiliationDescriptorID1" validUntil="2010-02-01T12:34:56Z" cacheDuration="PT9000S" >
        <AffiliateMember>test</AffiliateMember>
    </AffiliationDescriptor>
    <AffiliationDescriptor affiliationOwnerID="asdf" ID="theAffiliationDescriptorID2" validUntil="2010-02-01T12:34:56Z" cacheDuration="PT9000S" >
        <AffiliateMember>test2</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('More than one AffiliationDescriptor in the entity.');
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an EntityDescriptor from XML fails if multiple Organization objects are included.
     */
    public function testUnmarshallingWithMultipleOrganizations(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="theEntityID" ID="theID" validUntil="2010-01-01T12:34:56Z" cacheDuration="PT5000S" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
        <AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP" Location="https://idp.example.org/AttributeService" />
    </AttributeAuthorityDescriptor>
    <Organization>
        <OrganizationName xml:lang="en">orgNameTest (en)</OrganizationName>
        <OrganizationDisplayName xml:lang="en">orgDispNameTest (en)</OrganizationDisplayName>
        <OrganizationURL xml:lang="en">orgURL (en)</OrganizationURL>
    </Organization>
    <Organization>
        <OrganizationName xml:lang="no">orgNameTest (no)</OrganizationName>
        <OrganizationDisplayName xml:lang="no">orgDispNameTest (no)</OrganizationDisplayName>
        <OrganizationURL xml:lang="no">orgURL (no)</OrganizationURL>
    </Organization>
</EntityDescriptor>
XML
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('More than one Organization in the entity.');
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an EntityDescriptor from XML fails if both a RoleDescriptor and an AffiliationDescriptor
     * are included.
     */
    public function testUnmarshallingWithRoleandAffiliationDescriptors(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="theEntityID" ID="theID" validUntil="2010-01-01T12:34:56Z" cacheDuration="PT5000S" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
        <AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP" Location="https://idp.example.org/AttributeService" />
    </AttributeAuthorityDescriptor>
    <AffiliationDescriptor affiliationOwnerID="asdf" ID="theAffiliationDescriptorID" validUntil="2010-02-01T12:34:56Z" cacheDuration="PT9000S" >
        <AffiliateMember>test</AffiliateMember>
    </AffiliationDescriptor>
    <Organization>
        <OrganizationName xml:lang="en">orgNameTest (en)</OrganizationName>
        <OrganizationDisplayName xml:lang="en">orgDispNameTest (en)</OrganizationDisplayName>
        <OrganizationURL xml:lang="en">orgURL (en)</OrganizationURL>
    </Organization>
</EntityDescriptor>
XML
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'AffiliationDescriptor cannot be combined with other RoleDescriptor elements in EntityDescriptor.'
        );
        EntityDescriptor::fromXML($document->documentElement);
    }
}
