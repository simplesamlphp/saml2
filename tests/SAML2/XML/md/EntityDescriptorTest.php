<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\DOMDocumentFactory;
use SAML2\XML\md\EntityDescriptor;
use SAML2\XML\md\AffiliationDescriptor;
use SAML2\XML\md\Organization;
use SAML2\XML\md\AttributeAuthorityDescriptor;

/**
 * Class \SAML2\XML\md\EntityDescriptorTest
 */
class EntityDescriptorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMissingAffiliationId(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor>
        <AffiliateMember>test</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing affiliationOwnerID on AffiliationDescriptor.');
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * @return void
     */
    public function testMissingEntityId(): void
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
     * @return void
     */
    public function testMissingAffiliateMember(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor affiliationOwnerID="asdf">
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('List of affiliated members must not be empty.');
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * @return void
     */
    public function testMissingDescriptor(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
</EntityDescriptor>
XML
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.'
        );
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * @return void
     */
    public function testInvalidValidUntil(): void
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
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor affiliationOwnerID="asdf" ID="theAffiliationDescriptorID" validUntil="2010-02-01T12:34:56Z" cacheDuration="PT9000S" >
        <AffiliateMember>test</AffiliateMember>
        <AffiliateMember>test2</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $entityDescriptor = EntityDescriptor::fromXML($document->documentElement);

        $this->assertTrue($entityDescriptor instanceof EntityDescriptor);
        $this->assertEquals('theEntityID', $entityDescriptor->getEntityID());

        $roleDescriptor = $entityDescriptor->getRoleDescriptor();
        $this->assertTrue(empty($roleDescriptor));

        $affiliationDescriptor = $entityDescriptor->getAffiliationDescriptor();
        $this->assertTrue($affiliationDescriptor instanceof AffiliationDescriptor);
        $this->assertEquals('asdf', $affiliationDescriptor->getAffiliationOwnerID());
        $this->assertEquals('theAffiliationDescriptorID', $affiliationDescriptor->getID());
        $this->assertEquals(1265027696, $affiliationDescriptor->getValidUntil());
        $this->assertEquals('PT9000S', $affiliationDescriptor->getCacheDuration());
        $affiliateMembers = $affiliationDescriptor->getAffiliateMembers();
        $this->assertCount(2, $affiliateMembers);
        $this->assertEquals('test', $affiliateMembers[0]);
        $this->assertEquals('test2', $affiliateMembers[1]);
    }


    /**
     * @return void
     */
    public function testUnmarshalling2(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="theEntityID" ID="theID" validUntil="2010-01-01T12:34:56Z" cacheDuration="PT5000S" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">

    <AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
        <AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP" Location="https://idp.example.org/AttributeService" />
    </AttributeAuthorityDescriptor>

    <Organization>
        <OrganizationName xml:lang="no">orgNameTest (no)</OrganizationName>
        <OrganizationName xml:lang="en">orgNameTest (en)</OrganizationName>
        <OrganizationDisplayName xml:lang="no">orgDispNameTest (no)</OrganizationDisplayName>
        <OrganizationDisplayName xml:lang="en">orgDispNameTest (en)</OrganizationDisplayName>
        <OrganizationURL xml:lang="no">orgURL (no)</OrganizationURL>
        <OrganizationURL xml:lang="en">orgURL (en)</OrganizationURL>
    </Organization>
</EntityDescriptor>
XML
        );
        $entityDescriptor = EntityDescriptor::fromXML($document->documentElement);

        $this->assertTrue($entityDescriptor instanceof EntityDescriptor);
        $this->assertEquals('theEntityID', $entityDescriptor->getEntityID());
        $this->assertEquals('theID', $entityDescriptor->getID());
        $this->assertEquals(1262349296, $entityDescriptor->getValidUntil());
        $this->assertEquals('PT5000S', $entityDescriptor->getCacheDuration());

        $roleDescriptor = $entityDescriptor->getRoleDescriptor();
        $this->assertCount(1, $roleDescriptor);
        $this->assertTrue($roleDescriptor[0] instanceof AttributeAuthorityDescriptor);

        $o = $entityDescriptor->getOrganization();
        $this->assertTrue($o instanceof Organization);
        $this->assertCount(2, $o->getOrganizationName());
        $this->assertInstanceOf(OrganizationName::class, $o->getOrganizationName()[0]);
        $this->assertEquals('orgNameTest (no)', $o->getOrganizationName()[0]->getValue());
        $this->assertInstanceOf(OrganizationName::class, $o->getOrganizationName()[1]);
        $this->assertEquals('orgNameTest (en)', $o->getOrganizationName()[1]->getValue());
        $this->assertCount(2, $o->getOrganizationDisplayName());
        $this->assertInstanceOf(OrganizationDisplayName::class, $o->getOrganizationDisplayName()[0]);
        $this->assertEquals('orgDispNameTest (no)', $o->getOrganizationDisplayName()[0]->getValue());
        $this->assertInstanceOf(OrganizationDisplayName::class, $o->getOrganizationDisplayName()[1]);
        $this->assertEquals('orgDispNameTest (en)', $o->getOrganizationDisplayName()[1]->getValue());
        $this->assertCount(2, $o->getOrganizationURL());
        $this->assertEquals('orgURL (no)', $o->getOrganizationURL()["no"]);
        $this->assertEquals('orgURL (en)', $o->getOrganizationURL()["en"]);
    }
}
