<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AttributeAuthorityDescriptor;
use SimpleSAML\SAML2\XML\md\AttributeProfile;
use SimpleSAML\SAML2\XML\md\AttributeService;
use SimpleSAML\SAML2\XML\md\NameIDFormat;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\SignedElementTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\Utils as XMLUtils;

use function dirname;
use function strval;

/**
 * Tests for the AttributeAuthorityDescriptor class.
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMetadataDocument
 * @covers \SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AttributeAuthorityDescriptor
 * @package simplesamlphp/saml2
 */
final class AttributeAuthorityDescriptorTest extends TestCase
{
    use SerializableXMLTestTrait;
    use SignedElementTestTrait;


    /** @var \SimpleSAML\SAML2\XML\md\AttributeService */
    protected AttributeService $as;

    /** @var \SimpleSAML\SAML2\XML\md\AssertionIDRequestService */
    protected AssertionIDRequestService $aidrs;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = AttributeAuthorityDescriptor::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_AttributeAuthorityDescriptor.xml'
        );
        $this->as = new AttributeService(
            C::BINDING_SOAP,
            "https://IdentityProvider.com/SAML/AA/SOAP"
        );
        $this->aidrs = new AssertionIDRequestService(
            C::BINDING_URI,
            "https://IdentityProvider.com/SAML/AA/URI"
        );
    }


    // test marshalling


    /**
     * Test creating an AttributeAuthorityDescriptor from scratch
     */
    public function testMarshalling(): void
    {
        $attr1 = new Attribute(
            "urn:oid:1.3.6.1.4.1.5923.1.1.1.6",
            C::NAMEFORMAT_URI,
            "eduPersonPrincipalName"
        );

        $attr2 = new Attribute(
            "urn:oid:1.3.6.1.4.1.5923.1.1.1.1",
            C::NAMEFORMAT_URI,
            'eduPersonAffiliation',
            [
                new AttributeValue('member'),
                new AttributeValue('student'),
                new AttributeValue('faculty'),
                new AttributeValue('employee'),
                new AttributeValue('staff'),
            ]
        );
        $aad = new AttributeAuthorityDescriptor(
            [$this->as],
            [C::NS_SAMLP],
            [$this->aidrs],
            [
                new NameIDFormat(C::NAMEID_X509_SUBJECT_NAME),
                new NameIDFormat(C::NAMEID_PERSISTENT),
                new NameIDFormat(C::NAMEID_TRANSIENT),
            ],
            [
                new AttributeProfile(C::PROFILE_1),
                new AttributeProfile(C::PROFILE_2),
            ],
            [$attr1, $attr2]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($aad)
        );
    }

    /**
     * Test that creating an AttributeAuthorityDescriptor with no supported protocols fails.
     */
    public function testMarshallingWithoutSupportedProtocols(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'At least one protocol must be supported by this SimpleSAML\SAML2\XML\md\AttributeAuthorityDescriptor.'
        );
        new AttributeAuthorityDescriptor([$this->as], []);
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with an empty supported protocol fails.
     */
    public function testMarshallingWithEmptySupportedProtocols(): void
    {
        $this->expectException(SchemaViolationException::class);
        new AttributeAuthorityDescriptor([$this->as], ['']);
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with no AttributeService fails.
     */
    public function testMarshallingWithoutAttributeServices(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('AttributeAuthorityDescriptor must contain at least one AttributeService.');
        new AttributeAuthorityDescriptor([], [C::NS_SAMLP]);
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with an AttributeService of the wrong type fails.
     */
    public function testMarshallingWithWrongAttributeService(): void
    {
        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage('AttributeService is not an instance of EndpointType.');

        /** @psalm-suppress InvalidArgument */
        new AttributeAuthorityDescriptor(['string'], [C::NS_SAMLP]);
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor without optional parameters works.
     */
    public function testMarshallingWithoutOptionalParameters(): void
    {
        $aad = new AttributeAuthorityDescriptor([$this->as], [C::NS_SAMLP]);
        $this->assertEmpty($aad->getAssertionIDRequestServices());
        $this->assertEmpty($aad->getNameIDFormats());
        $this->assertEmpty($aad->getID());
        $this->assertEmpty($aad->getValidUntil());
        $this->assertEmpty($aad->getCacheDuration());
        $this->assertEmpty($aad->getExtensions());
        $this->assertEmpty($aad->getErrorURL());
        $this->assertEmpty($aad->getOrganization());
        $this->assertEmpty($aad->getKeyDescriptors());
        $this->assertEmpty($aad->getContactPersons());
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with empty AssertionIDRequestService works.
     */
    public function testMarshallingWithEmptyAssertionIDRequestService(): void
    {
        $aad = new AttributeAuthorityDescriptor([$this->as], [C::NS_SAMLP], []);
        $this->assertEmpty($aad->getAssertionIDRequestServices());
        $this->assertEmpty($aad->getNameIDFormats());
        $this->assertEmpty($aad->getID());
        $this->assertEmpty($aad->getValidUntil());
        $this->assertEmpty($aad->getCacheDuration());
        $this->assertEmpty($aad->getExtensions());
        $this->assertEmpty($aad->getErrorURL());
        $this->assertEmpty($aad->getOrganization());
        $this->assertEmpty($aad->getKeyDescriptors());
        $this->assertEmpty($aad->getContactPersons());
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with wrong AssertionIDRequestService fails.
     */
    public function testMarshallingWithWrongAssertionIDRequestService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'Expected an instance of SimpleSAML\SAML2\XML\md\AssertionIDRequestService. Got: string'
        );

        /** @psalm-suppress InvalidArgument */
        new AttributeAuthorityDescriptor([$this->as], [C::NS_SAMLP], ['x']);
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with an empty NameIDFormat fails.
     */
    public function testMarshallingWithEmptyNameIDFormat(): void
    {
        $this->expectException(SchemaViolationException::class);
        new AttributeAuthorityDescriptor([$this->as], [C::NS_SAMLP], [$this->aidrs], [new NameIDFormat('')]);
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with an empty AttributeProfile fails.
     */
    public function testMarshallingWithEmptyAttributeProfile(): void
    {
        $this->expectException(SchemaViolationException::class);
        new AttributeAuthorityDescriptor(
            [$this->as],
            [C::NS_SAMLP],
            [$this->aidrs],
            [new NameIDFormat(C::NAMEID_TRANSIENT)],
            [new AttributeProfile('')]
        );
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with wrong Attribute fails.
     */
    public function testMarshallingWithWrongAttribute(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'Expected an instance of SimpleSAML\SAML2\XML\saml\Attribute. Got: string'
        );

        /** @psalm-suppress InvalidArgument */
        new AttributeAuthorityDescriptor(
            [$this->as],
            [C::NS_SAMLP],
            [$this->aidrs],
            [new NameIDFormat(C::NAMEID_PERSISTENT)],
            [new AttributeProfile(C::PROFILE_1)], ['x']);
    }


    // test unmarshalling


    /**
     * Test creating an AttributeAuthorityDescriptor from XML
     */
    public function testUnmarshalling(): void
    {
        $aad = AttributeAuthorityDescriptor::fromXML($this->xmlRepresentation->documentElement);

        $as = $aad->getAttributeServices();
        $this->assertCount(1, $as, "Wrong number of AttributeService elements.");
        $this->assertEquals(C::BINDING_SOAP, $as[0]->getBinding());
        $this->assertEquals('https://IdentityProvider.com/SAML/AA/SOAP', $as[0]->getLocation());

        $aidrs = $aad->getAssertionIDRequestServices();
        $this->assertCount(1, $aidrs, "Wrong number of AssertionIDRequestService elements.");
        $this->assertEquals(C::BINDING_URI, $aidrs[0]->getBinding());
        $this->assertEquals('https://IdentityProvider.com/SAML/AA/URI', $aidrs[0]->getLocation());

        $nameIdFormats = $aad->getNameIDFormats();
        $this->assertCount(3, $nameIdFormats);
        $this->assertEquals(C::NAMEID_X509_SUBJECT_NAME, $nameIdFormats[0]->getContent());
        $this->assertEquals(C::NAMEID_PERSISTENT, $nameIdFormats[1]->getContent());
        $this->assertEquals(C::NAMEID_TRANSIENT, $nameIdFormats[2]->getContent());

        $attrs = $aad->getAttributes();
        $this->assertCount(2, $attrs, "Wrong number of attributes.");
        $this->assertEquals(
            [
                new AttributeProfile(C::PROFILE_1),
                new AttributeProfile(C::PROFILE_2),
            ],
            $aad->getAttributeProfiles()
        );
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor without any optional element works.
     */
    public function testUnmarshallingWithoutOptionalElements(): void
    {
        $mdns = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:md="{$mdns}">
  <md:AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
      Location="https://IdentityProvider.com/SAML/AA/SOAP"/>
</md:AttributeAuthorityDescriptor>
XML
        );

        $aad = AttributeAuthorityDescriptor::fromXML($document->documentElement);
        $this->assertEmpty($aad->getAssertionIDRequestServices());
        $this->assertEmpty($aad->getNameIDFormats());
        $this->assertEmpty($aad->getID());
        $this->assertEmpty($aad->getValidUntil());
        $this->assertEmpty($aad->getCacheDuration());
        $this->assertEmpty($aad->getExtensions());
        $this->assertEmpty($aad->getErrorURL());
        $this->assertEmpty($aad->getOrganization());
        $this->assertEmpty($aad->getKeyDescriptors());
        $this->assertEmpty($aad->getContactPersons());
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with an empty NameIDFormat fails.
     */
    public function testUnmarshallingWithEmptyNameIDFormat(): void
    {
        $mdns = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:md="{$mdns}">
  <md:AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
      Location="https://IdentityProvider.com/SAML/AA/SOAP"/>
  <md:NameIDFormat></md:NameIDFormat>
</md:AttributeAuthorityDescriptor>
XML
        );
        $this->expectException(SchemaViolationException::class);
        AttributeAuthorityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with an empty AttributeProfile fails.
     */
    public function testUnmarshallingWithEmptyAttributeProfile(): void
    {
        $mdns = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:md="{$mdns}">
  <md:AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
      Location="https://IdentityProvider.com/SAML/AA/SOAP"/>
  <md:AttributeProfile></md:AttributeProfile>
</md:AttributeAuthorityDescriptor>
XML
        );
        $this->expectException(SchemaViolationException::class);
        AttributeAuthorityDescriptor::fromXML($document->documentElement);
    }
}
