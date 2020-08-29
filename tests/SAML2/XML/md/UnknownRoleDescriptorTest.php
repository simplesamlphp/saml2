<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Chunk;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * This is a test for the UnknownRoleDescriptor class.
 *
 * Due to its nature, it doesn't make sense to test marshalling (creating) such an object, since in that case we
 * would know what object is this and we can model it properly.
 *
 * @covers \SimpleSAML\SAML2\XML\md\UnknownRoleDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMetadataDocument
 * @covers \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor
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
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_UnknownRoleDescriptor.xml'
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
