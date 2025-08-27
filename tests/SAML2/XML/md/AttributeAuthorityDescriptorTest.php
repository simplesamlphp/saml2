<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\{AnyURIListValue, SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\md\{
    AbstractMdElement,
    AbstractMetadataDocument,
    AbstractRoleDescriptor,
    AbstractRoleDescriptorType,
    AbstractSignedMdElement,
    AssertionIDRequestService,
    AttributeAuthorityDescriptor,
    AttributeProfile,
    AttributeService,
    NameIDFormat,
};
use SimpleSAML\SAML2\XML\saml\{Attribute, AttributeValue};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for the AttributeAuthorityDescriptor class.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
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
        self::$testedClass = AttributeAuthorityDescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AttributeAuthorityDescriptor.xml',
        );

        self::$as = new AttributeService(
            SAMLAnyURIValue::fromString(C::BINDING_SOAP),
            SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/AA/SOAP'),
        );

        self::$aidrs = new AssertionIDRequestService(
            SAMLAnyURIValue::fromString(C::BINDING_URI),
            SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/AA/URI'),
        );
    }


    // test marshalling


    /**
     * Test creating an AttributeAuthorityDescriptor from scratch
     */
    public function testMarshalling(): void
    {
        $attr1 = new Attribute(
            SAMLStringValue::fromString('urn:oid:1.3.6.1.4.1.5923.1.1.1.6'),
            SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
            SAMLStringValue::fromString("eduPersonPrincipalName"),
        );

        $attr2 = new Attribute(
            SAMLStringValue::fromString('urn:oid:1.3.6.1.4.1.5923.1.1.1.1'),
            SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
            SAMLStringValue::fromString('eduPersonAffiliation'),
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
            AnyURIListValue::fromString(C::NS_SAMLP),
            [self::$aidrs],
            [
                new NameIDFormat(
                    SAMLAnyURIValue::fromString(C::NAMEID_X509_SUBJECT_NAME),
                ),
                new NameIDFormat(
                    SAMLAnyURIValue::fromString(C::NAMEID_PERSISTENT),
                ),
                new NameIDFormat(
                    SAMLAnyURIValue::fromString(C::NAMEID_TRANSIENT),
                ),
            ],
            [
                new AttributeProfile(
                    SAMLAnyURIValue::fromString(C::PROFILE_1),
                ),
                new AttributeProfile(
                    SAMLAnyURIValue::fromString(C::PROFILE_2),
                ),
            ],
            [$attr1, $attr2],
            IDValue::fromString('phpunit'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($aad),
        );
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with an empty supported protocol fails.
     */
    public function testMarshallingWithEmptySupportedProtocols(): void
    {
        $this->expectException(ProtocolViolationException::class);
        new AttributeAuthorityDescriptor([self::$as], AnyURIListValue::fromString(''));
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with no AttributeService fails.
     */
    public function testMarshallingWithoutAttributeServices(): void
    {
        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('AttributeAuthorityDescriptor must contain at least one AttributeService.');
        new AttributeAuthorityDescriptor([], AnyURIListValue::fromString(C::NS_SAMLP));
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor without optional parameters works.
     */
    public function testMarshallingWithoutOptionalParameters(): void
    {
        $aad = new AttributeAuthorityDescriptor([self::$as], AnyURIListValue::fromString(C::NS_SAMLP));
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
        $aad = new AttributeAuthorityDescriptor([self::$as], AnyURIListValue::fromString(C::NS_SAMLP), []);
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


    // test unmarshalling


    /**
     * Test that creating an AttributeAuthorityDescriptor without any optional element works.
     */
    public function testUnmarshallingWithoutOptionalElements(): void
    {
        $mdns = C::NS_MD;
        $document = DOMDocumentFactory::fromString(
            <<<XML
<md:AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:md="{$mdns}">
  <md:AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
      Location="https://IdentityProvider.com/SAML/AA/SOAP"/>
</md:AttributeAuthorityDescriptor>
XML
            ,
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
        $document = DOMDocumentFactory::fromString(
            <<<XML
<md:AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:md="{$mdns}">
  <md:AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
      Location="https://IdentityProvider.com/SAML/AA/SOAP"/>
  <md:NameIDFormat></md:NameIDFormat>
</md:AttributeAuthorityDescriptor>
XML
            ,
        );
        $this->expectException(ProtocolViolationException::class);
        AttributeAuthorityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an AttributeAuthorityDescriptor with an empty AttributeProfile fails.
     */
    public function testUnmarshallingWithEmptyAttributeProfile(): void
    {
        $mdns = C::NS_MD;
        $document = DOMDocumentFactory::fromString(
            <<<XML
<md:AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:md="{$mdns}">
  <md:AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
      Location="https://IdentityProvider.com/SAML/AA/SOAP"/>
  <md:AttributeProfile></md:AttributeProfile>
</md:AttributeAuthorityDescriptor>
XML
            ,
        );
        $this->expectException(ProtocolViolationException::class);
        AttributeAuthorityDescriptor::fromXML($document->documentElement);
    }
}
