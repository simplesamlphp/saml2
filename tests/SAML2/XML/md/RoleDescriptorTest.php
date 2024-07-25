<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\AbstractMetadataDocument;
use SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor;
use SimpleSAML\SAML2\XML\md\AbstractRoleDescriptorType;
use SimpleSAML\SAML2\XML\md\AbstractSignedMdElement;
use SimpleSAML\SAML2\XML\md\Company;
use SimpleSAML\SAML2\XML\md\ContactPerson;
use SimpleSAML\SAML2\XML\md\EmailAddress;
use SimpleSAML\SAML2\XML\md\EncryptionMethod;
use SimpleSAML\SAML2\XML\md\Extensions;
use SimpleSAML\SAML2\XML\md\GivenName;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\SAML2\XML\md\Organization;
use SimpleSAML\SAML2\XML\md\OrganizationDisplayName;
use SimpleSAML\SAML2\XML\md\OrganizationName;
use SimpleSAML\SAML2\XML\md\OrganizationURL;
use SimpleSAML\SAML2\XML\md\SurName;
use SimpleSAML\SAML2\XML\md\TelephoneNumber;
use SimpleSAML\SAML2\XML\md\UnknownRoleDescriptor;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\CustomRoleDescriptor;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;

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


    public function setUp(): void
    {
        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_RoleDescriptor.xml',
        );
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
        $attr_cp_1 = new XMLAttribute('urn:test:something', 'test', 'attr1', 'testval1');
        $attr_cp_2 = new XMLAttribute('urn:test:something', 'test', 'attr2', 'testval2');
        $attr_3 = new XMLAttribute('urn:x-simplesamlphp:namespace', 'ssp', 'phpunit', 'test');

        $roleDescriptor = new CustomRoleDescriptor(
            [
                new Chunk(DOMDocumentFactory::fromString(
                    '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Some</ssp:Chunk>',
                )->documentElement),
            ],
            [C::NS_SAMLP, C::PROTOCOL],
            'TheID',
            new DateTimeImmutable('2009-02-13T23:31:30Z'),
            'PT5000S',
            new Extensions([new Chunk(
                DOMDocumentFactory::fromString(
                    '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Some</ssp:Chunk>',
                )->documentElement,
            )]),
            'https://error.reporting/',
            [
                new KeyDescriptor(
                    new KeyInfo([new KeyName('IdentityProvider.com SSO Signing Key')]),
                    'signing',
                ),
                new KeyDescriptor(
                    new KeyInfo([new KeyName('IdentityProvider.com SSO Encryption Key')]),
                    'encryption',
                    [new EncryptionMethod(C::KEY_TRANSPORT_OAEP_MGF1P)],
                ),
            ],
            new Organization(
                [new OrganizationName('en', 'Identity Providers R US')],
                [new OrganizationDisplayName('en', 'Identity Providers R US, a Division of Lerxst Corp.')],
                [new OrganizationURL('en', 'https://IdentityProvider.com')],
            ),
            [
                new ContactPerson(
                    contactType: 'other',
                    company: new Company('Test Company'),
                    givenName: new GivenName('John'),
                    surName: new SurName('Doe'),
                    emailAddress: [
                        new EmailAddress('mailto:jdoe@test.company'),
                        new EmailAddress('mailto:john.doe@test.company'),
                    ],
                    telephoneNumber: [new TelephoneNumber('1-234-567-8901')],
                    namespacedAttribute: [$attr_cp_1, $attr_cp_2],
                ),
                new ContactPerson(
                    contactType: 'technical',
                    telephoneNumber: [new TelephoneNumber('1-234-567-8901')],
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

        $this->assertInstanceOf(CustomRoleDescriptor::class, $descriptor);
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
        $this->assertEquals('2009-02-13T23:31:30Z', $descriptor->getValidUntil()->format(C::DATETIME_FORMAT));
        $this->assertEquals('PT5000S', $descriptor->getCacheDuration());
        $this->assertEquals('https://error.reporting/', $descriptor->getErrorURL());

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
        $element = self::$xmlRepresentation->documentElement;
        $element->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:ssp',
            'urn:x-simplesamlphp:namespace',
        );

        $type = new XMLAttribute(C::NS_XSI, 'xsi', 'type', 'ssp:UnknownRoleDescriptorType');
        $type->toXML($element);

        $descriptor = UnknownRoleDescriptor::fromXML($element);

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
        $this->assertEquals('2009-02-13T23:31:30Z', $descriptor->getValidUntil()->format(C::DATETIME_FORMAT));
        $this->assertEquals('PT5000S', $descriptor->getCacheDuration());
        $this->assertEquals('https://error.reporting/', $descriptor->getErrorURL());

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

        $this->assertEquals($element->ownerDocument->saveXML($element), strval($chunk));
    }


    /**
     * Test creating an UnknownRoleDescriptor from an XML that lacks supported protocols.
     */
    public function testUnmarshallingWithoutSupportedProtocols(): void
    {
        self::$xmlRepresentation->documentElement->removeAttribute('protocolSupportEnumeration');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage(
            'Missing \'protocolSupportEnumeration\' attribute on md:RoleDescriptor.',
        );

        UnknownRoleDescriptor::fromXML(self::$xmlRepresentation->documentElement);
    }


    /**
     * Test creating an UnknownRoleDescriptor from an XML that lacks supported protocols.
     */
    public function testUnmarshallingWithEmptySupportedProtocols(): void
    {
        self::$xmlRepresentation->documentElement->setAttribute('protocolSupportEnumeration', '');

        $this->expectException(ProtocolViolationException::class);

        UnknownRoleDescriptor::fromXML(self::$xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an UnknownRoleDescriptor from XML fails if errorURL is not a valid URL.
     */
    public function testUnmarshallingWithInvalidErrorURL(): void
    {
        self::$xmlRepresentation->documentElement->setAttribute('errorURL', 'not a URL');

        $this->expectException(ProtocolViolationException::class);

        UnknownRoleDescriptor::fromXML(self::$xmlRepresentation->documentElement);
    }
}
