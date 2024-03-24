<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\AbstractMetadataDocument;
use SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor;
use SimpleSAML\SAML2\XML\md\AbstractRoleDescriptorType;
use SimpleSAML\SAML2\XML\md\AbstractSignedMdElement;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AttributeAuthorityDescriptor;
use SimpleSAML\SAML2\XML\md\AttributeProfile;
use SimpleSAML\SAML2\XML\md\AttributeService;
use SimpleSAML\SAML2\XML\md\NameIDFormat;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for the AttributeAuthorityDescriptor class.
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(AttributeAuthorityDescriptor::class)]
#[CoversClass(AbstractRoleDescriptor::class)]
#[CoversClass(AbstractRoleDescriptorType::class)]
#[CoversClass(AbstractMetadataDocument::class)]
#[CoversClass(AbstractSignedMdElement::class)]
#[CoversClass(AbstractMdElement::class)]
final class AttributeAuthorityDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /** @var \SimpleSAML\SAML2\XML\md\AttributeService */
    private static AttributeService $as;

    /** @var \SimpleSAML\SAML2\XML\md\AssertionIDRequestService */
    private static AssertionIDRequestService $aidrs;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = AttributeAuthorityDescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AttributeAuthorityDescriptor.xml',
        );

        self::$as = new AttributeService(
            C::BINDING_SOAP,
            "https://IdentityProvider.com/SAML/AA/SOAP",
        );

        self::$aidrs = new AssertionIDRequestService(
            C::BINDING_URI,
            "https://IdentityProvider.com/SAML/AA/URI",
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
            "eduPersonPrincipalName",
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
            ],
        );
        $aad = new AttributeAuthorityDescriptor(
            [self::$as],
            [C::NS_SAMLP],
            [self::$aidrs],
            [
                new NameIDFormat(C::NAMEID_X509_SUBJECT_NAME),
                new NameIDFormat(C::NAMEID_PERSISTENT),
                new NameIDFormat(C::NAMEID_TRANSIENT),
            ],
            [
                new AttributeProfile(C::PROFILE_1),
                new AttributeProfile(C::PROFILE_2),
            ],
            [$attr1, $attr2],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($aad),
        );
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with no supported protocols fails.
     */
    public function testMarshallingWithoutSupportedProtocols(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'At least one protocol must be supported by this md:AttributeAuthorityDescriptor.',
        );
        new AttributeAuthorityDescriptor([self::$as], []);
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with an empty supported protocol fails.
     */
    public function testMarshallingWithEmptySupportedProtocols(): void
    {
        $this->expectException(AssertionFailedException::class);
        new AttributeAuthorityDescriptor([self::$as], []);
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
     * Test that creating an AttributeAuthorityDescriptor without optional parameters works.
     */
    public function testMarshallingWithoutOptionalParameters(): void
    {
        $aad = new AttributeAuthorityDescriptor([self::$as], [C::NS_SAMLP]);
        $this->assertEmpty($aad->getAssertionIDRequestService());
        $this->assertEmpty($aad->getNameIDFormat());
        $this->assertEmpty($aad->getID());
        $this->assertEmpty($aad->getValidUntil());
        $this->assertEmpty($aad->getCacheDuration());
        $this->assertEmpty($aad->getExtensions());
        $this->assertEmpty($aad->getErrorURL());
        $this->assertEmpty($aad->getOrganization());
        $this->assertEmpty($aad->getKeyDescriptor());
        $this->assertEmpty($aad->getContactPerson());
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with empty AssertionIDRequestService works.
     */
    public function testMarshallingWithEmptyAssertionIDRequestService(): void
    {
        $aad = new AttributeAuthorityDescriptor([self::$as], [C::NS_SAMLP], []);
        $this->assertEmpty($aad->getAssertionIDRequestService());
        $this->assertEmpty($aad->getNameIDFormat());
        $this->assertEmpty($aad->getID());
        $this->assertEmpty($aad->getValidUntil());
        $this->assertEmpty($aad->getCacheDuration());
        $this->assertEmpty($aad->getExtensions());
        $this->assertEmpty($aad->getErrorURL());
        $this->assertEmpty($aad->getOrganization());
        $this->assertEmpty($aad->getKeyDescriptor());
        $this->assertEmpty($aad->getContactPerson());
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with an empty NameIDFormat fails.
     */
    public function testMarshallingWithEmptyNameIDFormat(): void
    {
        $this->expectException(SchemaViolationException::class);
        new AttributeAuthorityDescriptor([self::$as], [C::NS_SAMLP], [self::$aidrs], [new NameIDFormat('')]);
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with an empty AttributeProfile fails.
     */
    public function testMarshallingWithEmptyAttributeProfile(): void
    {
        $this->expectException(SchemaViolationException::class);
        new AttributeAuthorityDescriptor(
            [self::$as],
            [C::NS_SAMLP],
            [self::$aidrs],
            [new NameIDFormat(C::NAMEID_TRANSIENT)],
            [new AttributeProfile('')],
        );
    }


    // test unmarshalling


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
        $this->assertEmpty($aad->getAssertionIDRequestService());
        $this->assertEmpty($aad->getNameIDFormat());
        $this->assertEmpty($aad->getID());
        $this->assertEmpty($aad->getValidUntil());
        $this->assertEmpty($aad->getCacheDuration());
        $this->assertEmpty($aad->getExtensions());
        $this->assertEmpty($aad->getErrorURL());
        $this->assertEmpty($aad->getOrganization());
        $this->assertEmpty($aad->getKeyDescriptor());
        $this->assertEmpty($aad->getContactPerson());
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
