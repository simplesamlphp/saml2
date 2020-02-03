<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Tests for the md:EntitiesDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
class EntitiesDescriptorTest extends TestCase
{
    protected $document;


    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $dsns = XMLSecurityDSig::XMLDSIGNS;
        $samlns = Constants::NS_SAML;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:EntitiesDescriptor xmlns:md="{$mdns}" Name="Federation">
  <md:EntityDescriptor entityID="https://IdentityProvider.com/SAML">
    <md:IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" WantAuthnRequestsSigned="true">
      <md:KeyDescriptor use="signing">
        <ds:KeyInfo xmlns:ds="{$dsns}">
          <ds:KeyName>IdentityProvider.com SSO Key</ds:KeyName>
        </ds:KeyInfo>
      </md:KeyDescriptor>
      <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP" Location="https://IdentityProvider.com/SAML/SLO/SOAP"/>
      <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://IdentityProvider.com/SAML/SLO/Browser" ResponseLocation="https://IdentityProvider.com/SAML/SLO/Response"/>
      <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName</md:NameIDFormat>
      <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:persistent</md:NameIDFormat>
      <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:transient</md:NameIDFormat>
      <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://IdentityProvider.com/SAML/SSO/Browser"/>
      <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="https://IdentityProvider.com/SAML/SSO/Browser"/>
      <saml:Attribute xmlns:saml="{$samlns}" Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.6" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri" FriendlyName="eduPersonPrincipalName"></saml:Attribute>
      <saml:Attribute xmlns:saml="{$samlns}" Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.1" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri" FriendlyName="eduPersonAffiliation">
        <saml:AttributeValue>member</saml:AttributeValue>
        <saml:AttributeValue>student</saml:AttributeValue>
        <saml:AttributeValue>faculty</saml:AttributeValue>
        <saml:AttributeValue>employee</saml:AttributeValue>
        <saml:AttributeValue>staff</saml:AttributeValue>
      </saml:Attribute>
    </md:IDPSSODescriptor>
    <md:Organization>
      <md:OrganizationName xml:lang="en">Identity Providers R US</md:OrganizationName>
      <md:OrganizationDisplayName xml:lang="en">Identity Providers R US, a Division of Lerxst Corp.</md:OrganizationDisplayName>
      <md:OrganizationURL xml:lang="en">https://IdentityProvider.com</md:OrganizationURL>
    </md:Organization>
  </md:EntityDescriptor>
</md:EntitiesDescriptor>
XML
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(EntitiesDescriptor::fromXML($this->document->documentElement))))
        );
    }
}
