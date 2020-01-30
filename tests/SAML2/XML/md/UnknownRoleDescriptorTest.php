<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;

/**
 * This is a test for the UnknownRoleDescriptor class.
 *
 * Due to its nature, it doesn't make sense to test marshalling (creating) such an object, since in that case we
 * would know what object is this and we can model it properly.
 *
 * @package simplesamlphp/saml2
 */
class UnknownRoleDescriptorTest extends TestCase
{
    protected $document;


    public function setUp(): void
    {
        $namespace = 'namespace:uri';
        $mdns = Constants::NS_MD;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<ns:SomeRoleDescriptor xmlns:ns="{$namespace}" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S" protocolSupportEnumeration="protocol1 protocol2">
  <md:Extensions xmlns:md="{$mdns}">
    <md:SomeUnknownExtension attr="attrval">value</md:SomeUnknownExtension>
  </md:Extensions>
  <ns:SomeElement>SomeValue</ns:SomeElement>
</ns:SomeRoleDescriptor>
XML
        );
    }


    /**
     * Test unmarshalling an unknown object as a RoleDescriptor.
     */
    public function testUnmarshalling(): void
    {
        $descriptor = UnknownRoleDescriptor::fromXML($this->document->documentElement);
        $this->assertEquals('TheID', $descriptor->getID());
        $this->assertEquals(1234567890, $descriptor->getValidUntil());
        $this->assertEquals('PT5000S', $descriptor->getCacheDuration());
        $xml = $descriptor->getXML();
        $this->assertInstanceOf(Chunk::class, $xml);
        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($descriptor));
        $this->assertInstanceOf(Extensions::class, $descriptor->getExtensions());
        $extensions = $descriptor->getExtensions()->getList();
        $this->assertCount(1, $extensions);
        $this->assertInstanceOf(Chunk::class, $extensions[0]);
        $this->assertEquals(Constants::NS_MD, $extensions[0]->getNamespaceURI());
        $this->assertEquals('SomeUnknownExtension', $extensions[0]->getLocalName());
    }


    /**
     * Test creating an AttributeAuthorityDescriptor from an XML that lacks supported protocols.
     */
    public function testUnmarshallingWithoutSupportedProtocols(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'Missing \'protocolSupportEnumeration\' attribute from md:UnknownRoleDescriptor.'
        );
        $this->document->documentElement->removeAttribute('protocolSupportEnumeration');
        UnknownRoleDescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test creating an AttributeAuthorityDescriptor from an XML that lacks supported protocols.
     */
    public function testUnmarshallingWithEmptySupportedProtocols(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot specify an empty string as a supported protocol.');
        $this->document->documentElement->setAttribute('protocolSupportEnumeration', '');
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
