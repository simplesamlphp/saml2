<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Exception\MissingAttributeException;
use SAML2\XML\Chunk;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * This is a test for the UnknownRoleDescriptor class.
 *
 * Due to its nature, it doesn't make sense to test marshalling (creating) such an object, since in that case we
 * would know what object is this and we can model it properly.
 *
 * @package simplesamlphp/saml2
 */
final class UnknownRoleDescriptorTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $namespace = 'namespace:uri';
        $mdns = Constants::NS_MD;
        $dsns = XMLSecurityDSig::XMLDSIGNS;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<ns:SomeRoleDescriptor xmlns:ns="{$namespace}" xmlns:md="{$mdns}" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S" protocolSupportEnumeration="protocol1 protocol2" errorURL="https://error.reporting/">
  <md:KeyDescriptor use="signing">
    <ds:KeyInfo xmlns:ds="{$dsns}">
      <ds:KeyName>IdentityProvider.com SSO Signing Key</ds:KeyName>
    </ds:KeyInfo>
  </md:KeyDescriptor>
  <md:KeyDescriptor use="encryption">
    <ds:KeyInfo xmlns:ds="{$dsns}">
      <ds:KeyName>IdentityProvider.com SSO Encryption Key</ds:KeyName>
    </ds:KeyInfo>
    <md:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p"></md:EncryptionMethod>
  </md:KeyDescriptor>
  <md:Organization>
    <md:OrganizationName xml:lang="en">Identity Providers R US</md:OrganizationName>
    <md:OrganizationDisplayName xml:lang="en">Identity Providers R US, a Division of Lerxst Corp.</md:OrganizationDisplayName>
    <md:OrganizationURL xml:lang="en">https://IdentityProvider.com</md:OrganizationURL>
  </md:Organization>
  <md:ContactPerson contactType="other" test:attr1="testval1" test:attr2="testval2" xmlns:test="urn:test">
    <md:Company>Test Company</md:Company>
    <md:GivenName>John</md:GivenName>
    <md:SurName>Doe</md:SurName>
    <md:EmailAddress>mailto:jdoe@test.company</md:EmailAddress>
    <md:EmailAddress>mailto:john.doe@test.company</md:EmailAddress>
    <md:TelephoneNumber>1-234-567-8901</md:TelephoneNumber>
  </md:ContactPerson>
  <md:ContactPerson contactType="technical">
    <md:TelephoneNumber>1-234-567-8901</md:TelephoneNumber>
  </md:ContactPerson>
  <md:Extensions xmlns:md="{$mdns}">
    <md:SomeUnknownExtension attr="attrval">value</md:SomeUnknownExtension>
  </md:Extensions>
  <ns:SomeElement>SomeValue</ns:SomeElement>
</ns:SomeRoleDescriptor>
XML
        );
    }


    // test unmarshalling


    /**
     * Test unmarshalling an unknown object as a RoleDescriptor.
     */
    public function testUnmarshalling(): void
    {
        $descriptor = UnknownRoleDescriptor::fromXML($this->document->documentElement);

        $this->assertCount(2, $descriptor->getKeyDescriptors());
        $this->assertInstanceOf(KeyDescriptor::class, $descriptor->getKeyDescriptors()[0]);
        $this->assertInstanceOf(KeyDescriptor::class, $descriptor->getKeyDescriptors()[1]);
        $this->assertEquals(
            ['protocol1', 'protocol2'],
            $descriptor->getProtocolSupportEnumeration()
        );
        $this->assertInstanceOf(Organization::class, $descriptor->getOrganization());
        $this->assertCount(2, $descriptor->getContactPersons());
        $this->assertInstanceOf(ContactPerson::class, $descriptor->getContactPersons()[0]);
        $this->assertInstanceOf(ContactPerson::class, $descriptor->getContactPersons()[1]);
        $this->assertEquals('TheID', $descriptor->getID());
        $this->assertEquals(1234567890, $descriptor->getValidUntil());
        $this->assertEquals('PT5000S', $descriptor->getCacheDuration());
        $this->assertEquals('https://error.reporting/', $descriptor->getErrorURL());

        $xml = $descriptor->getXML();
        $this->assertEquals('SomeRoleDescriptor', $xml->getLocalName());

        $extElement = $descriptor->getExtensions();
        $this->assertInstanceOf(Extensions::class, $extElement);

        $extensions = $extElement->getList();
        $this->assertCount(1, $extensions);
        $this->assertInstanceOf(Chunk::class, $extensions[0]);
        $this->assertEquals(Constants::NS_MD, $extensions[0]->getNamespaceURI());
        $this->assertEquals('SomeUnknownExtension', $extensions[0]->getLocalName());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($descriptor));
    }


    /**
     * Test creating an UnknownRoleDescriptor from an XML that lacks supported protocols.
     */
    public function testUnmarshallingWithoutSupportedProtocols(): void
    {
        $this->document->documentElement->removeAttribute('protocolSupportEnumeration');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage(
            'Missing \'protocolSupportEnumeration\' attribute on md:UnknownRoleDescriptor.'
        );

        UnknownRoleDescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test creating an UnknownRoleDescriptor from an XML that lacks supported protocols.
     */
    public function testUnmarshallingWithEmptySupportedProtocols(): void
    {
        $this->document->documentElement->setAttribute('protocolSupportEnumeration', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Cannot specify an empty string as a supported protocol.');

        UnknownRoleDescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an UnknownRoleDescriptor from XML fails if errorURL is not a valid URL.
     */
    public function testUnmarshallingWithInvalidErrorURL(): void
    {
        $this->document->documentElement->setAttribute('errorURL', 'not a URL');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RoleDescriptor errorURL is not a valid URL.');

        UnknownRoleDescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test serialization and unserialization of unknown role descriptors.
     */
    public function testSerialization(): void
    {
        $descriptor = UnknownRoleDescriptor::fromXML($this->document->documentElement);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize($descriptor)))
        );
    }
}
