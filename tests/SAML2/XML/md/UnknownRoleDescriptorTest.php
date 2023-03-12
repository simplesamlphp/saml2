<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\ContactPerson;
use SimpleSAML\SAML2\XML\md\Extensions;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\SAML2\XML\md\Organization;
use SimpleSAML\SAML2\XML\md\UnknownRoleDescriptor;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;
use SimpleSAML\XMLSecurity\XMLSecurityDSig;

use function dirname;
use function strval;

/**
 * This is a test for the UnknownRoleDescriptor class.
 *
 * Due to its nature, it doesn't make sense to test marshalling (creating) such an object, since in that case we
 * would know what object is this and we can model it properly.
 *
 * @covers \SimpleSAML\SAML2\XML\md\UnknownRoleDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMetadataDocument
 * @covers \SimpleSAML\SAML2\XML\md\AbstractSignedMdElement
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 *
 * @package simplesamlphp/saml2
 */
final class UnknownRoleDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/simplesamlphp.xsd';

        $this->testedClass = UnknownRoleDescriptor::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/md_UnknownRoleDescriptor.xml',
        );
    }


    // test unmarshalling


    /**
     * Test unmarshalling an unknown object as a RoleDescriptor.
     */
    public function testUnmarshalling(): void
    {
        $descriptor = UnknownRoleDescriptor::fromXML($this->xmlRepresentation->documentElement);

        $this->assertCount(2, $descriptor->getKeyDescriptor());
        $this->assertInstanceOf(KeyDescriptor::class, $descriptor->getKeyDescriptor()[0]);
        $this->assertInstanceOf(KeyDescriptor::class, $descriptor->getKeyDescriptor()[1]);
        $this->assertEquals(
            [C::NS_SAMLP, C::PROTOCOL],
            $descriptor->getProtocolSupportEnumeration(),
        );
        $this->assertInstanceOf(Organization::class, $descriptor->getOrganization());
        $this->assertCount(2, $descriptor->getContactPerson());
        $this->assertInstanceOf(ContactPerson::class, $descriptor->getContactPerson()[0]);
        $this->assertInstanceOf(ContactPerson::class, $descriptor->getContactPerson()[1]);
        $this->assertEquals('TheID', $descriptor->getID());
        $this->assertEquals(1234567890, $descriptor->getValidUntil());
        $this->assertEquals('PT5000S', $descriptor->getCacheDuration());
        $this->assertEquals('https://error.reporting/', $descriptor->getErrorURL());

        $xml = $descriptor->getXML();
        $this->assertEquals('RoleDescriptor', $xml->localName);

        $extElement = $descriptor->getExtensions();
        $this->assertInstanceOf(Extensions::class, $extElement);

        $extensions = $extElement->getList();
        $this->assertCount(1, $extensions);
        $this->assertInstanceOf(Chunk::class, $extensions[0]);
        $this->assertEquals('urn:x-simplesamlphp:namespace', $extensions[0]->getNamespaceURI());
        $this->assertEquals('Chunk', $extensions[0]->getLocalName());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($descriptor),
        );
    }


    /**
     * Test creating an UnknownRoleDescriptor from an XML that lacks supported protocols.
     */
    public function testUnmarshallingWithoutSupportedProtocols(): void
    {
        $this->xmlRepresentation->documentElement->removeAttribute('protocolSupportEnumeration');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage(
            'Missing \'protocolSupportEnumeration\' attribute on md:UnknownRoleDescriptor.',
        );

        UnknownRoleDescriptor::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test creating an UnknownRoleDescriptor from an XML that lacks supported protocols.
     */
    public function testUnmarshallingWithEmptySupportedProtocols(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('protocolSupportEnumeration', '');

        $this->expectException(SchemaViolationException::class);

        UnknownRoleDescriptor::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an UnknownRoleDescriptor from XML fails if errorURL is not a valid URL.
     */
    public function testUnmarshallingWithInvalidErrorURL(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('errorURL', 'not a URL');

        $this->expectException(SchemaViolationException::class);

        UnknownRoleDescriptor::fromXML($this->xmlRepresentation->documentElement);
    }
}
