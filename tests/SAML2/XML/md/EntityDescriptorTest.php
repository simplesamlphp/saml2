<?php

namespace SAML2\XML\md;

use SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\md\EntityDescriptorTest
 */
class EntityDescriptorTest extends \PHPUnit_Framework_TestCase
{
    public function testMissingAffiliationId()
    {
        $document = DOMDocumentFactory::fromString(
        <<<XML
<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor>
        <AffiliateMember>test</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->setExpectedException('Exception', 'Missing affiliationOwnerID on AffiliationDescriptor.');
        new EntityDescriptor($document->firstChild);
    }

    public function testMissingEntityId()
    {
        $document = DOMDocumentFactory::fromString(
        <<<XML
<EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor affiliationOwnerID="asdf">
        <AffiliateMember>test</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->setExpectedException('Exception', 'Missing required attribute entityID on EntityDescriptor.');
        new EntityDescriptor($document->firstChild);
    }

    public function testMissingAffiliateMember()
    {
        $document = DOMDocumentFactory::fromString(
        <<<XML
<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor affiliationOwnerID="asdf">
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->setExpectedException('Exception', 'Missing AffiliateMember in AffiliationDescriptor.');
        new EntityDescriptor($document->firstChild);
    }

    public function testMissingDescriptor()
    {
        $document = DOMDocumentFactory::fromString(
        <<<XML
<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
</EntityDescriptor>
XML
        );
        $this->setExpectedException('Exception', 'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.');
        new EntityDescriptor($document->firstChild);
    }

    public function testInvalidValidUntil()
    {
        $document = DOMDocumentFactory::fromString(
        <<<XML
<EntityDescriptor validUntil="asdf" entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor affiliationOwnerID="asd">
        <AffiliateMember>test</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->setExpectedException('Exception', 'Invalid SAML2 timestamp passed to xsDateTimeToTimestamp: asdf');
        new EntityDescriptor($document->firstChild);
    }

    public function testUnmarshalling()
    {
        $document = DOMDocumentFactory::fromString(
        <<<XML
<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor affiliationOwnerID="asdf" ID="theAffiliationDescriptorID" validUntil="2010-02-01T12:34:56Z" cacheDuration="PT9000S" >
        <AffiliateMember>test</AffiliateMember>
        <AffiliateMember>test2</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $entityDescriptor = new EntityDescriptor($document->firstChild);

        $this->assertTrue($entityDescriptor instanceof EntityDescriptor);
        $this->assertEquals('theEntityID', $entityDescriptor->entityID);

        $this->assertTrue(empty($entityDescriptor->RoleDescriptor));

        $affiliationDescriptor = $entityDescriptor->AffiliationDescriptor;
        $this->assertTrue($affiliationDescriptor instanceof AffiliationDescriptor);
        $this->assertEquals('asdf', $affiliationDescriptor->affiliationOwnerID);
        $this->assertEquals('theAffiliationDescriptorID', $affiliationDescriptor->ID);
        $this->assertEquals(1265027696, $affiliationDescriptor->validUntil);
        $this->assertEquals('PT9000S', $affiliationDescriptor->cacheDuration);
        $this->assertCount(2, $affiliationDescriptor->AffiliateMember);
        $this->assertEquals('test', $affiliationDescriptor->AffiliateMember[0]);
        $this->assertEquals('test2', $affiliationDescriptor->AffiliateMember[1]);
    }

    public function testUnmarshalling2()
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
        $entityDescriptor = new EntityDescriptor($document->firstChild);

        $this->assertTrue($entityDescriptor instanceof EntityDescriptor);
        $this->assertEquals('theEntityID', $entityDescriptor->entityID);
        $this->assertEquals('theID', $entityDescriptor->ID);
        $this->assertEquals(1262349296, $entityDescriptor->validUntil);
        $this->assertEquals('PT5000S', $entityDescriptor->cacheDuration);

        $this->assertCount(1, $entityDescriptor->RoleDescriptor);
        $this->assertTrue($entityDescriptor->RoleDescriptor[0] instanceof AttributeAuthorityDescriptor);

        $o = $entityDescriptor->Organization;
        $this->assertTrue($o instanceof Organization);
        $this->assertCount(2, $o->OrganizationName);
        $this->assertEquals('orgNameTest (no)', $o->OrganizationName["no"]);
        $this->assertEquals('orgNameTest (en)', $o->OrganizationName["en"]);
        $this->assertCount(2, $o->OrganizationDisplayName);
        $this->assertEquals('orgDispNameTest (no)', $o->OrganizationDisplayName["no"]);
        $this->assertEquals('orgDispNameTest (en)', $o->OrganizationDisplayName["en"]);
        $this->assertCount(2, $o->OrganizationURL);
        $this->assertEquals('orgURL (no)', $o->OrganizationURL["no"]);
        $this->assertEquals('orgURL (en)', $o->OrganizationURL["en"]);
    }
}
