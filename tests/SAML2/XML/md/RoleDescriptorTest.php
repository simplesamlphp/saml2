<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\{AbstractContainer, ContainerSingleton};
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\{
    AnyURIListValue,
    EmailAddressValue,
    KeyTypesValue,
    SAMLAnyURIValue,
    SAMLDateTimeValue,
    SAMLStringValue,
};
use SimpleSAML\SAML2\XML\md\{
    AbstractMdElement,
    AbstractMetadataDocument,
    AbstractRoleDescriptor,
    AbstractRoleDescriptorType,
    AbstractSignedMdElement,
    Company,
    ContactPerson,
    EmailAddress,
    EncryptionMethod,
    Extensions,
    GivenName,
    KeyDescriptor,
    KeyTypesEnum,
    Organization,
    OrganizationDisplayName,
    OrganizationName,
    OrganizationURL,
    SurName,
    TelephoneNumber,
    UnknownRoleDescriptor,
};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\CustomRoleDescriptor;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\{Chunk, DOMDocumentFactory};
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\{AnyURIValue, DurationValue, IDValue, LanguageValue, StringValue};
use SimpleSAML\XMLSecurity\XML\ds\{KeyInfo, KeyName};

use function dirname;
use function strval;

/**
 * This is a test for the UnknownRoleDescriptor class.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(UnknownRoleDescriptor::class)]
#[CoversClass(AbstractRoleDescriptor::class)]
#[CoversClass(AbstractRoleDescriptorType::class)]
#[CoversClass(AbstractMetadataDocument::class)]
#[CoversClass(AbstractSignedMdElement::class)]
#[CoversClass(AbstractMdElement::class)]
final class RoleDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /** @var \SimpleSAML\SAML2\Compat\AbstractContainer */
    private static AbstractContainer $containerBackup;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$containerBackup = ContainerSingleton::getInstance();

        self::$schemaFile = dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/schemas/simplesamlphp.xsd';

        self::$testedClass = AbstractRoleDescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_RoleDescriptor.xml',
        );

        $container = clone self::$containerBackup;
        $container->registerExtensionHandler(CustomRoleDescriptor::class);
        ContainerSingleton::setContainer($container);
    }


    /**
     */
    public static function tearDownAfterClass(): void
    {
        ContainerSingleton::setContainer(self::$containerBackup);
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $attr_cp_1 = new XMLAttribute('urn:test:something', 'test', 'attr1', StringValue::fromString('testval1'));
        $attr_cp_2 = new XMLAttribute('urn:test:something', 'test', 'attr2', StringValue::fromString('testval2'));
        $attr_3 = new XMLAttribute('urn:x-simplesamlphp:namespace', 'ssp', 'phpunit', StringValue::fromString('test'));

        $roleDescriptor = new CustomRoleDescriptor(
            [
                new Chunk(DOMDocumentFactory::fromString(
                    '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Some</ssp:Chunk>',
                )->documentElement),
            ],
            AnyURIListValue::fromArray([C::NS_SAMLP, C::PROTOCOL]),
            IDValue::fromString('TheID'),
            SAMLDateTimeValue::fromString('2009-02-13T23:31:30Z'),
            DurationValue::fromString('PT5000S'),
            new Extensions([
                new Chunk(
                    DOMDocumentFactory::fromString(
                        '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Some</ssp:Chunk>',
                    )->documentElement,
                ),
            ]),
            SAMLAnyURIValue::fromString('https://error.reporting/'),
            [
                new KeyDescriptor(
                    new KeyInfo([
                        new KeyName(
                            StringValue::fromString('IdentityProvider.com SSO Signing Key'),
                        ),
                    ]),
                    KeyTypesValue::fromEnum(KeyTypesEnum::SIGNING),
                ),
                new KeyDescriptor(
                    new KeyInfo([
                        new KeyName(
                            StringValue::fromString('IdentityProvider.com SSO Encryption Key'),
                        ),
                    ]),
                    KeyTypesValue::fromEnum(KeyTypesEnum::ENCRYPTION),
                    [
                        new EncryptionMethod(
                            AnyURIValue::fromString(C::KEY_TRANSPORT_OAEP_MGF1P),
                        ),
                    ],
                ),
            ],
            new Organization(
                [
                    new OrganizationName(
                        LanguageValue::fromString('en'),
                        SAMLStringValue::fromString('Identity Providers R US'),
                    ),
                ],
                [
                    new OrganizationDisplayName(
                        LanguageValue::fromString('en'),
                        SAMLStringValue::fromString('Identity Providers R US, a Division of Lerxst Corp.'),
                    ),
                ],
                [
                    new OrganizationURL(
                        LanguageValue::fromString('en'),
                        SAMLAnyURIValue::fromString('https://IdentityProvider.com'),
                    ),
                ],
            ),
            [
                new ContactPerson(
                    contactType: SAMLStringValue::fromString('other'),
                    company: new Company(
                        SAMLStringValue::fromString('Test Company'),
                    ),
                    givenName: new GivenName(
                        SAMLStringValue::fromString('John'),
                    ),
                    surName: new SurName(
                        SAMLStringValue::fromString('Doe'),
                    ),
                    emailAddress: [
                        new EmailAddress(
                            EmailAddressValue::fromString('mailto:jdoe@test.company'),
                        ),
                        new EmailAddress(
                            EmailAddressValue::fromString('mailto:john.doe@test.company'),
                        ),
                    ],
                    telephoneNumber: [
                        new TelephoneNumber(
                            SAMLStringValue::fromString('1-234-567-8901'),
                        ),
                    ],
                    namespacedAttribute: [$attr_cp_1, $attr_cp_2],
                ),
                new ContactPerson(
                    contactType: SAMLStringValue::fromString('technical'),
                    telephoneNumber: [
                        new TelephoneNumber(
                            SAMLStringValue::fromString('1-234-567-8901'),
                        ),
                    ],
                ),
            ],
            [$attr_3],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($roleDescriptor),
        );
    }


    // test unmarshalling


    /**
     * Test unmarshalling a known object as a RoleDescriptor.
     */
    public function testUnmarshalling(): void
    {
        $descriptor = AbstractRoleDescriptor::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertInstanceOf(AbstractRoleDescriptor::class, $descriptor);
        $this->assertCount(2, $descriptor->getKeyDescriptor());
        $this->assertInstanceOf(KeyDescriptor::class, $descriptor->getKeyDescriptor()[0]);
        $this->assertInstanceOf(KeyDescriptor::class, $descriptor->getKeyDescriptor()[1]);
        $this->assertEquals(
            [C::NS_SAMLP, C::PROTOCOL],
            $descriptor->getProtocolSupportEnumeration()->toArray(),
        );
        $this->assertInstanceOf(Organization::class, $descriptor->getOrganization());
        $this->assertCount(2, $descriptor->getContactPerson());
        $this->assertInstanceOf(ContactPerson::class, $descriptor->getContactPerson()[0]);
        $this->assertInstanceOf(ContactPerson::class, $descriptor->getContactPerson()[1]);
        $this->assertEquals('TheID', $descriptor->getID());
        $this->assertEquals('2009-02-13T23:31:30Z', $descriptor->getValidUntil()->getValue());
        $this->assertEquals('PT5000S', $descriptor->getCacheDuration()->getValue());
        $this->assertEquals('https://error.reporting/', $descriptor->getErrorURL()->getValue());

        $extElement = $descriptor->getExtensions();
        $this->assertInstanceOf(Extensions::class, $extElement);

        $extensions = $extElement->getList();
        $this->assertCount(1, $extensions);
        $this->assertInstanceOf(Chunk::class, $extensions[0]);
        $this->assertEquals('urn:x-simplesamlphp:namespace', $extensions[0]->getNamespaceURI());
        $this->assertEquals('Chunk', $extensions[0]->getLocalName());

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($descriptor),
        );
    }


    /**
     */
    public function testUnmarshallingUnregistered(): void
    {
        $element = clone self::$xmlRepresentation->documentElement;
        $element->setAttributeNS(C::NS_XSI, 'xsi:type', 'ssp:UnknownRoleDescriptorType');

        $type = new XMLAttribute(C::NS_XSI, 'xsi', 'type', StringValue::fromString('ssp:UnknownRoleDescriptorType'));
        $type->toXML($element);

        $descriptor = AbstractRoleDescriptor::fromXML($element);
        $this->assertInstanceOf(UnknownRoleDescriptor::class, $descriptor);

        $this->assertCount(2, $descriptor->getKeyDescriptor());
        $this->assertInstanceOf(KeyDescriptor::class, $descriptor->getKeyDescriptor()[0]);
        $this->assertInstanceOf(KeyDescriptor::class, $descriptor->getKeyDescriptor()[1]);
        $this->assertEquals(
            [C::NS_SAMLP, C::PROTOCOL],
            $descriptor->getProtocolSupportEnumeration()->toArray(),
        );
        $this->assertInstanceOf(Organization::class, $descriptor->getOrganization());
        $this->assertCount(2, $descriptor->getContactPerson());
        $this->assertInstanceOf(ContactPerson::class, $descriptor->getContactPerson()[0]);
        $this->assertInstanceOf(ContactPerson::class, $descriptor->getContactPerson()[1]);
        $this->assertEquals('TheID', $descriptor->getID()->getValue());
        $this->assertEquals('2009-02-13T23:31:30Z', $descriptor->getValidUntil()->getValue());
        $this->assertEquals('PT5000S', $descriptor->getCacheDuration()->getValue());
        $this->assertEquals('https://error.reporting/', $descriptor->getErrorURL()->getValue());

        $chunk = $descriptor->getRawRoleDescriptor();
        $this->assertEquals('md', $chunk->getPrefix());
        $this->assertEquals('RoleDescriptor', $chunk->getLocalName());
        $this->assertEquals(C::NS_MD, $chunk->getNamespaceURI());

        $extElement = $descriptor->getExtensions();
        $this->assertInstanceOf(Extensions::class, $extElement);

        $extensions = $extElement->getList();
        $this->assertCount(1, $extensions);
        $this->assertInstanceOf(Chunk::class, $extensions[0]);
        $this->assertEquals('urn:x-simplesamlphp:namespace', $extensions[0]->getNamespaceURI());
        $this->assertEquals('Chunk', $extensions[0]->getLocalName());

        $this->assertEquals($element->ownerDocument?->saveXML($element), strval($descriptor));
    }


    /**
     * Test creating an UnknownRoleDescriptor from an XML that lacks supported protocols.
     */
    public function testUnmarshallingWithoutSupportedProtocols(): void
    {
        $element = clone self::$xmlRepresentation->documentElement;
        $element->removeAttribute('protocolSupportEnumeration');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage(
            'Missing \'protocolSupportEnumeration\' attribute on md:RoleDescriptor.',
        );

        UnknownRoleDescriptor::fromXML($element);
    }


    /**
     * Test creating an UnknownRoleDescriptor from an XML that lacks supported protocols.
     */
    public function testUnmarshallingWithEmptySupportedProtocols(): void
    {
        $element = clone self::$xmlRepresentation->documentElement;
        $element->setAttribute('protocolSupportEnumeration', '');

        $this->expectException(ProtocolViolationException::class);

        UnknownRoleDescriptor::fromXML($element);
    }
}
