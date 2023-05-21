<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\EntityDescriptor;
use SimpleSAML\SAML2\XML\md\AffiliationDescriptor;
use SimpleSAML\SAML2\XML\md\Organization;
use SimpleSAML\SAML2\XML\md\AttributeAuthorityDescriptor;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\md\EntityDescriptorTest
 */
class EntityDescriptorTest extends TestCase
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
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing affiliationOwnerID on AffiliationDescriptor.');
        new EntityDescriptor($document->firstChild);
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
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing required attribute entityID on EntityDescriptor.');
        new EntityDescriptor($document->firstChild);
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
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing AffiliateMember in AffiliationDescriptor.');
        new EntityDescriptor($document->firstChild);
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
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.'
        );
        new EntityDescriptor($document->firstChild);
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
        $this->expectException(AssertionFailedException::class);
        new EntityDescriptor($document->firstChild);
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
        $entityDescriptor = new EntityDescriptor($document->firstChild);

        $this->assertInstanceOf(EntityDescriptor::class, $entityDescriptor);
        $this->assertEquals('theEntityID', $entityDescriptor->getEntityID());

        $roleDescriptor = $entityDescriptor->getRoleDescriptor();
        $this->assertTrue(empty($roleDescriptor));

        $affiliationDescriptor = $entityDescriptor->getAffiliationDescriptor();
        $this->assertInstanceOf(AffiliationDescriptor::class, $affiliationDescriptor);
        $this->assertEquals('asdf', $affiliationDescriptor->getAffiliationOwnerID());
        $this->assertEquals('theAffiliationDescriptorID', $affiliationDescriptor->getID());
        $this->assertEquals(1265027696, $affiliationDescriptor->getValidUntil());
        $this->assertEquals('PT9000S', $affiliationDescriptor->getCacheDuration());
        $affiliateMember = $affiliationDescriptor->getAffiliateMember();
        $this->assertCount(2, $affiliateMember);
        $this->assertEquals('test', $affiliateMember[0]->getContent());
        $this->assertEquals('test2', $affiliateMember[1]->getContent());
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
        <OrganizationURL xml:lang="no">https://example.org/no</OrganizationURL>
        <OrganizationURL xml:lang="en">https://example.org/en</OrganizationURL>
    </Organization>
</EntityDescriptor>
XML
        );
        $entityDescriptor = new EntityDescriptor($document->firstChild);

        $this->assertInstanceOf(EntityDescriptor::class, $entityDescriptor);
        $this->assertEquals('theEntityID', $entityDescriptor->getEntityID());
        $this->assertEquals('theID', $entityDescriptor->getID());
        $this->assertEquals(1262349296, $entityDescriptor->getValidUntil());
        $this->assertEquals('PT5000S', $entityDescriptor->getCacheDuration());

        $roleDescriptor = $entityDescriptor->getRoleDescriptor();
        $this->assertCount(1, $roleDescriptor);
        $this->assertInstanceOf(AttributeAuthorityDescriptor::class, $roleDescriptor[0]);

        $o = $entityDescriptor->getOrganization();
        $this->assertTrue($o instanceof Organization);
        $this->assertCount(2, $o->getOrganizationName());
        $this->assertEquals('no', $o->getOrganizationName()[0]->getLanguage());
        $this->assertEquals('orgNameTest (no)', $o->getOrganizationName()[0]->getContent());
        $this->assertEquals('en', $o->getOrganizationName()[1]->getLanguage());
        $this->assertEquals('orgNameTest (en)', $o->getOrganizationName()[1]->getContent());
        $this->assertCount(2, $o->getOrganizationDisplayName());
        $this->assertEquals('no', $o->getOrganizationDisplayName()[0]->getLanguage());
        $this->assertEquals('orgDispNameTest (no)', $o->getOrganizationDisplayName()[0]->getContent());
        $this->assertEquals('en', $o->getOrganizationDisplayName()[1]->getLanguage());
        $this->assertEquals('orgDispNameTest (en)', $o->getOrganizationDisplayName()[1]->getContent());
        $this->assertCount(2, $o->getOrganizationURL());
        $this->assertEquals('no', $o->getOrganizationURL()[0]->getLanguage());
        $this->assertEquals('https://example.org/no', $o->getOrganizationURL()[0]->getContent());
        $this->assertEquals('en', $o->getOrganizationURL()[1]->getLanguage());
        $this->assertEquals('https://example.org/en', $o->getOrganizationURL()[1]->getContent());
    }
}
