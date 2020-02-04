<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\DOMDocumentFactory;
use SAML2\XML\md\EntityDescriptor;
use SAML2\XML\md\AffiliationDescriptor;
use SAML2\XML\md\Organization;
use SAML2\XML\md\AttributeAuthorityDescriptor;

/**
 * Class \SAML2\XML\md\EntityDescriptorTest
 */
class EntityDescriptorTest extends TestCase
{

    /**
     * Test that creating an EntityDescriptor without any descriptors fails.
     */
    public function testMarshallingWithoutDescriptors(): void
    {
        $this->expectExceptionMessage(
            'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.'
        );
        new EntityDescriptor('entityID');
    }


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
<EntityDescriptor entityID="theEntityID" xmlns="urn:oasis:names:tc:SAML:2.0:metadata" validUntil="2010-02-01T12:34:56Z">
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

    /**
     * @return void
     */
    public function testUnmarshalling3(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" ID="EBc60ffd0dd599c012758d159a8b8495d1aba5cdf6" entityID="https://engine.test.example.edu/authentication/idp/metadata" validUntil="2020-02-05T15:01:31Z">
  <md:IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    <md:Extensions>
      <mdui:UIInfo xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui">
        <mdui:DisplayName xml:lang="nl">Example TEST EB nl</mdui:DisplayName>
        <mdui:DisplayName xml:lang="en">Example TEST EB en</mdui:DisplayName>
        <mdui:Description xml:lang="nl">Example TEST</mdui:Description>
        <mdui:Description xml:lang="en">Example TEST</mdui:Description>
        <mdui:Logo height="96" width="96">https://engine.test.example.edu/images/logo.png</mdui:Logo>
      </mdui:UIInfo>
    </md:Extensions>
    <md:KeyDescriptor use="signing">
      <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
        <ds:X509Data>
          <ds:X509Certificate>MIIDXzCCAkegAwIBAgIJAO/SRRMh1qu5MA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMMBkVuZ2luZTERMA8GA1UECwwIU2VydmljZXMxEzARBgNVBAoMCk9wZW5Db25leHQxCzAJBgNVBAYTAk5MMB4XDTE0MTAyMzA4MDIwMloXDTI0MTAyMjA4MDIwMlowRjEPMA0GA1UEAwwGRW5naW5lMREwDwYDVQQLDAhTZXJ2aWNlczETMBEGA1UECgwKT3BlbkNvbmV4dDELMAkGA1UEBhMCTkwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC8k23xFL7q2I13NgI0qpv7idgfQv1VyEoANY1+ot1Mkt30dDjGeUPd5A+KqDZpH+NA/oOrgG9dXSyrx4vAhTqomJ1RlMnoohTj3fAQC5+eMP5mlmzzzvme8dY4wOOq1ynGtpVDqqmBz1gzhzin0++0XOuRideo3/H6jZX0QSOwVe/KH7RJjW08+ECHLVZYPhFdLVTkQhGl0sox1HaV2O+CQhokrJzSjquf/WOAmv3vNWVZbvf2n9ICPSvY5A0Q4aXLScvx8qxJ3FrY9xCd07sGdGV2BTog+LEgBDvrnM/E9Wy7HQE8c/dIQ9WguV1kk1ApVYeSOrs9XnURW4zFP/sRAgMBAAGjUDBOMB0GA1UdDgQWBBSgDb9JMhj9nS9IgLn5Z63cpI/CLjAfBgNVHSMEGDAWgBSgDb9JMhj9nS9IgLn5Z63cpI/CLjAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4IBAQBZO+zUTIJnIBmGG0s/8AQhkeJixx9ow413uZSMhPYFMkj+Zxoxl9g1y63BVzchxXKjVqOkV2gMGCw1n5vDzsPTZRbzuXkbTk9fWp9+CYOc+hcOT29xGWNwORF+p7yGK4BRQx2VemQE9IoAo6h7Mcz83k3KXzAyOWvfpI9HNM3K/my7+cO3TY3ua/gzkS70pqANJZHZXcKmnbzsimIL7N1ro9pk2M9XChHqrFwVXBESwpc3Ff3AsARGQsMO4SjywuwJ2Wr7HeWp1YHFucpYekNuE9UMfZE1Zd0f/TAcv8nr7c4rdt1vRwk8lPXZ8LaAtnfbAi6sC9gIfB6hHmFukEyC</ds:X509Certificate>
        </ds:X509Data>
      </ds:KeyInfo>
    </md:KeyDescriptor>
    <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:persistent</md:NameIDFormat>
    <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:transient</md:NameIDFormat>
    <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified</md:NameIDFormat>
    <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://engine.test.example.edu/authentication/idp/single-sign-on"/>
  </md:IDPSSODescriptor>
  <md:Organization>
    <md:OrganizationName xml:lang="nl">Example TEST nl</md:OrganizationName>
    <md:OrganizationName xml:lang="en">Example TEST en</md:OrganizationName>
    <md:OrganizationDisplayName xml:lang="nl">Example TEST org nl</md:OrganizationDisplayName>
    <md:OrganizationDisplayName xml:lang="en">Example TEST org en</md:OrganizationDisplayName>
    <md:OrganizationURL xml:lang="nl">https://support.example.edu/</md:OrganizationURL>
    <md:OrganizationURL xml:lang="en">https://support.example.edu/en</md:OrganizationURL>
  </md:Organization>
  <md:ContactPerson contactType="support">
    <md:GivenName>Example TEST help</md:GivenName>
    <md:SurName>desk</md:SurName>
    <md:EmailAddress>mailto:help@example.edu</md:EmailAddress>
  </md:ContactPerson>
  <md:ContactPerson contactType="technical">
    <md:GivenName>Example Tech</md:GivenName>
    <md:SurName>BOFH</md:SurName>
    <md:EmailAddress>mailto:root@example.edu</md:EmailAddress>
  </md:ContactPerson>
  <md:ContactPerson contactType="administrative">
    <md:GivenName>Example admin</md:GivenName>
    <md:SurName>admin</md:SurName>
    <md:EmailAddress>mailto:info@example.edu</md:EmailAddress>
  </md:ContactPerson>
  <md:AdditionalMetadataLocation namespace="somemd">https://example.edu/some/metadata.xml</md:AdditionalMetadataLocation>
  <md:AdditionalMetadataLocation namespace="mymd">https://example.edu/more/metadata.xml</md:AdditionalMetadataLocation>
</md:EntityDescriptor>
XML
        );
        $entityDescriptor = EntityDescriptor::fromXML($document->documentElement);

        $this->assertTrue($entityDescriptor instanceof EntityDescriptor);
        $this->assertEquals('https://engine.test.example.edu/authentication/idp/metadata', $entityDescriptor->getEntityID());
        $this->assertEquals('EBc60ffd0dd599c012758d159a8b8495d1aba5cdf6', $entityDescriptor->getID());
        $this->assertEquals(1580914891, $entityDescriptor->getValidUntil());
        $this->assertNull($entityDescriptor->getCacheDuration());

        $roleDescriptor = $entityDescriptor->getRoleDescriptor();
        $this->assertCount(1, $roleDescriptor);
        $this->assertTrue($roleDescriptor[0] instanceof IDPSSODescriptor);

        $o = $entityDescriptor->getOrganization();
        $this->assertTrue($o instanceof Organization);
        $this->assertCount(2, $o->getOrganizationName());
        $this->assertCount(2, $o->getOrganizationDisplayName());
        $this->assertInstanceOf(OrganizationDisplayName::class, $o->getOrganizationDisplayName()[0]);
        $this->assertEquals('Example TEST org nl', $o->getOrganizationDisplayName()[0]->getValue());
        $this->assertInstanceOf(OrganizationDisplayName::class, $o->getOrganizationDisplayName()[1]);
        $this->assertEquals('Example TEST org en', $o->getOrganizationDisplayName()[1]->getValue());
        $this->assertCount(2, $o->getOrganizationURL());
        $this->assertEquals('https://support.example.edu/', $o->getOrganizationURL()["nl"]);
        $this->assertEquals('https://support.example.edu/en', $o->getOrganizationURL()["en"]);


        $cp = $entityDescriptor->getContactPerson();

        $this->assertCount(3, $cp);

        $this->assertInstanceOf(ContactPerson::class, $cp[0]);
        $this->assertEquals("desk", $cp[0]->getSurName());
        $this->assertEquals("root@example.edu", $cp[1]->getEmailAddresses()[0]);
        $this->assertEquals("administrative", $cp[2]->getContactType());

        $aml = $entityDescriptor->getAdditionalMetadataLocation();
        $this->assertCount(2, $aml);
        $this->assertInstanceOf(AdditionalMetadataLocation::class, $aml[1]);
        $this->assertEquals("mymd", $aml[1]->getNamespace());
        $this->assertEquals("https://example.edu/more/metadata.xml", $aml[1]->getLocation());
    }

    /**
     * @return void
     */
    public function testMarshallingBasic(): void
    {
        $entityid = "urn:example:entity";
        $id = "_5A3CHB081";
        $now = time();
        $duration = "P2Y6M5DT12H35M30S";

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
            true);

        $ed = new EntityDescriptor($entityid, $id, $now, $duration, null, [$idpssod]);

        $element = $ed->toXML();

        $this->assertEquals("md:EntityDescriptor", $element->tagName);
        $this->assertEquals($entityid, $element->getAttribute("entityID"));
        $this->assertEquals($id, $element->getAttribute("ID"));
        $this->assertEquals(substr(gmdate('c', $now),0,19)."Z", $element->getAttribute("validUntil"));
        $this->assertEquals($duration, $element->getAttribute("cacheDuration"));

        $this->assertCount(1, $element->childNodes);
        $this->assertEquals("md:IDPSSODescriptor", $element->childNodes[0]->tagName);
    }
}
