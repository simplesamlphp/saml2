<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DateTimeImmutable;
use DOMText;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\AbstractMetadataDocument;
use SimpleSAML\SAML2\XML\md\AbstractSignedMdElement;
use SimpleSAML\SAML2\XML\md\AdditionalMetadataLocation;
use SimpleSAML\SAML2\XML\md\AffiliateMember;
use SimpleSAML\SAML2\XML\md\AffiliationDescriptor;
use SimpleSAML\SAML2\XML\md\AttributeAuthorityDescriptor;
use SimpleSAML\SAML2\XML\md\AttributeService;
use SimpleSAML\SAML2\XML\md\AuthnAuthorityDescriptor;
use SimpleSAML\SAML2\XML\md\AuthnQueryService;
use SimpleSAML\SAML2\XML\md\AuthzService;
use SimpleSAML\SAML2\XML\md\ContactPerson;
use SimpleSAML\SAML2\XML\md\EmailAddress;
use SimpleSAML\SAML2\XML\md\EntityDescriptor;
use SimpleSAML\SAML2\XML\md\Extensions;
use SimpleSAML\SAML2\XML\md\IDPSSODescriptor;
use SimpleSAML\SAML2\XML\md\Organization;
use SimpleSAML\SAML2\XML\md\OrganizationDisplayName;
use SimpleSAML\SAML2\XML\md\OrganizationName;
use SimpleSAML\SAML2\XML\md\OrganizationURL;
use SimpleSAML\SAML2\XML\md\PDPDescriptor;
use SimpleSAML\SAML2\XML\md\SingleSignOnService;
use SimpleSAML\SAML2\XML\md\UnknownRoleDescriptor;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\UsagePolicy;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function str_pad;
use function strval;

/**
 * Class \SAML2\XML\md\EntityDescriptorTest
 *
 * @package simplesamlphp/saml2
 */
#[CoversClass(EntityDescriptor::class)]
#[CoversClass(AbstractSignedMdElement::class)]
#[CoversClass(AbstractMetadataDocument::class)]
#[CoversClass(AbstractMdElement::class)]
final class EntityDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = EntityDescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_EntityDescriptor.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an EntityDescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $attr1 = new XMLAttribute('urn:test:something', 'test', 'attr1', 'testval1');

        $entityid = C::ENTITY_IDP;
        $id = "_5A3CHB081";
        $now = 1580895565;
        $duration = "P2Y6M5DT12H35M30S";
        $idpssod = new IDPSSODescriptor(
            [
                new SingleSignOnService(
                    C::BINDING_HTTP_REDIRECT,
                    'https://engine.test.example.edu/authentication/idp/single-sign-on',
                ),
            ],
            [C::NS_SAMLP],
        );
        $attrad = new AttributeAuthorityDescriptor(
            [
                new AttributeService(
                    C::BINDING_SOAP,
                    'https://idp.example.org/AttributeService',
                ),
            ],
            [C::NS_SAMLP],
        );
        $authnad = new AuthnAuthorityDescriptor(
            [
                new AuthnQueryService(
                    C::BINDING_HTTP_REDIRECT,
                    'http://www.example.com/aqs',
                ),
            ],
            [C::NS_SAMLP],
        );
        $pdpd = new PDPDescriptor(
            [
                new AuthzService(
                    C::BINDING_SOAP,
                    'https://IdentityProvider.com/SAML/AA/SOAP',
                ),
            ],
            [C::NS_SAMLP],
        );
        $org = new Organization(
            [new OrganizationName('en', 'orgNameTest (en)')],
            [new OrganizationDisplayName('en', 'orgDispNameTest (en)')],
            [new OrganizationURL('en', 'https://IdentityProvider.com')],
        );
        $contacts = [
            new ContactPerson(
                contactType: 'support',
                emailAddress: [new EmailAddress('help@example.edu')],
            ),
            new ContactPerson(
                contactType: 'technical',
                emailAddress: [new EmailAddress('root@example.edu')],
            ),
            new ContactPerson(
                contactType: 'administrative',
                emailAddress: [new EmailAddress('info@example.edu')],
            ),
        ];
        $mdloc = [
            new AdditionalMetadataLocation(C::NAMESPACE, 'https://example.edu/some/metadata.xml'),
            new AdditionalMetadataLocation(C::NAMESPACE, 'https://example.edu/more/metadata.xml'),
        ];
        $extensions = new Extensions([
            new PublicationInfo(
                publisher: 'http://publisher.ra/',
                creationInstant: new DateTimeImmutable('2020-02-03T13:46:24Z'),
                usagePolicy: [new UsagePolicy('en', 'http://publisher.ra/policy.txt')]
            )
        ]);

        $ed = new EntityDescriptor(
            entityId: $entityid,
            id: $id,
            validUntil: new DateTimeImmutable('2020-02-05T09:39:25Z'),
            cacheDuration: $duration,
            extensions: $extensions,
            roleDescriptor: [
                $idpssod,
                $attrad,
                $authnad,
                $pdpd,
            ],
            organization: $org,
            contactPerson: $contacts,
            additionalMetadataLocation: $mdloc,
            namespacedAttribute: [$attr1],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($ed),
        );
    }


    /**
     * Test that creating an EntityDescriptor from scratch works when we are using an AffiliationDescriptor.
     */
    public function testMarshallingWithAffiliationDescriptor(): void
    {
        $ns_md = C::NS_MD;
        $entity_idp = C::ENTITY_IDP;
        $entity_other = C::ENTITY_OTHER;

        $document = DOMDocumentFactory::fromString(<<<XML
<md:EntityDescriptor xmlns:md="{$ns_md}" ID="_5A3CHB081" validUntil="2020-02-05T09:39:25Z"
    cacheDuration="P2Y6M5DT12H35M30S" entityID="{$entity_idp}">
  <md:Extensions>
    <mdrpi:PublicationInfo xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
        publisher="http://publisher.ra/" creationInstant="2020-02-03T13:46:24Z">
      <mdrpi:UsagePolicy xml:lang="en">http://publisher.ra/policy.txt</mdrpi:UsagePolicy>
    </mdrpi:PublicationInfo>
  </md:Extensions>
  <md:AffiliationDescriptor affiliationOwnerID="{$entity_idp}">
    <md:AffiliateMember>{$entity_other}</md:AffiliateMember>
  </md:AffiliationDescriptor>
  <md:Organization>
    <md:OrganizationName xml:lang="en">orgNameTest (en)</md:OrganizationName>
    <md:OrganizationDisplayName xml:lang="en">orgDispNameTest (en)</md:OrganizationDisplayName>
    <md:OrganizationURL xml:lang="en">https://IdentityProvider.com</md:OrganizationURL>
  </md:Organization>
  <md:ContactPerson contactType="support">
    <md:EmailAddress>mailto:help@example.edu</md:EmailAddress>
  </md:ContactPerson>
  <md:ContactPerson contactType="technical">
    <md:EmailAddress>mailto:root@example.edu</md:EmailAddress>
  </md:ContactPerson>
  <md:ContactPerson contactType="administrative">
    <md:EmailAddress>mailto:info@example.edu</md:EmailAddress>
  </md:ContactPerson>
  <md:AdditionalMetadataLocation
      namespace="urn:x-simplesamlphp:namespace">https://example.edu/some/metadata.xml</md:AdditionalMetadataLocation>
  <md:AdditionalMetadataLocation
      namespace="urn:x-simplesamlphp:namespace">https://example.edu/more/metadata.xml</md:AdditionalMetadataLocation>
</md:EntityDescriptor>
XML
        );

        $entityid = C::ENTITY_IDP;
        $id = "_5A3CHB081";
        $duration = "P2Y6M5DT12H35M30S";
        $ad = new AffiliationDescriptor(C::ENTITY_IDP, [new AffiliateMember(C::ENTITY_OTHER)]);
        $org = new Organization(
            [new OrganizationName('en', 'orgNameTest (en)')],
            [new OrganizationDisplayName('en', 'orgDispNameTest (en)')],
            [new OrganizationURL('en', 'https://IdentityProvider.com')],
        );
        $contacts = [
            new ContactPerson(
                contactType: 'support',
                emailAddress: [new EmailAddress('help@example.edu')],
            ),
            new ContactPerson(
                contactType: 'technical',
                emailAddress: [new EmailAddress('root@example.edu')],
            ),
            new ContactPerson(
                contactType: 'administrative',
                emailAddress: [new EmailAddress('info@example.edu')],
            ),
        ];
        $mdloc = [
            new AdditionalMetadataLocation(C::NAMESPACE, 'https://example.edu/some/metadata.xml'),
            new AdditionalMetadataLocation(C::NAMESPACE, 'https://example.edu/more/metadata.xml'),
        ];
        $extensions = new Extensions([
            new PublicationInfo(
                publisher: 'http://publisher.ra/',
                creationInstant: new DateTimeImmutable('2020-02-03T13:46:24Z'),
                usagePolicy: [new UsagePolicy('en', 'http://publisher.ra/policy.txt')],
            ),
        ]);

        $ed = new EntityDescriptor(
            entityId: $entityid,
            id: $id,
            validUntil: new DateTimeImmutable('2020-02-05T09:39:25Z'),
            cacheDuration: $duration,
            extensions: $extensions,
            affiliationDescriptor: $ad,
            organization: $org,
            contactPerson: $contacts,
            additionalMetadataLocation: $mdloc,
        );
        $this->assertEquals($entityid, $ed->getEntityID());
        $this->assertEquals($id, $ed->getID());
        $this->assertEquals('2020-02-05T09:39:25Z', $ed->getValidUntil()->format(C::DATETIME_FORMAT));
        $this->assertEquals($duration, $ed->getCacheDuration());
        $this->assertEmpty($ed->getRoleDescriptor());
        $this->assertInstanceOf(AffiliationDescriptor::class, $ed->getAffiliationDescriptor());
        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($ed),
        );
    }


    /**
     * Test that creating an EntityDescriptor from scratch without any descriptors fails.
     */
    public function testMarshallingWithoutDescriptors(): void
    {
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.',
        );
        new EntityDescriptor(C::ENTITY_SP);
    }


    /**
     * Test that creating an EntityDescriptor from scratch with both RoleDescriptors and an AffiliationDescriptor fails.
     */
    public function testMarshallingWithAffiliationAndRoleDescriptors(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $affiliationDescriptor = new AffiliationDescriptor(C::ENTITY_IDP, [new AffiliateMember(C::ENTITY_SP)]);
        $affiliationDescriptor->toXML($xmlRepresentation->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'AffiliationDescriptor cannot be combined with other RoleDescriptor elements in EntityDescriptor.',
        );

        EntityDescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EntityDescriptor from scratch fails if an empty entityID is provided.
     */
    public function testMarshallingWithEmptyEntityID(): void
    {
        $this->expectException(SchemaViolationException::class);
        new EntityDescriptor(
            entityId: '',
            affiliationDescriptor: new AffiliationDescriptor(C::ENTITY_IDP, [new AffiliateMember(C::ENTITY_SP)]),
        );
    }


    /**
     * Test that creating an EntityDescriptor from scratch with a very long entityID fails.
     */
    public function testMarshallingWithLongEntityID(): void
    {
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            sprintf('The entityID attribute cannot be longer than %d characters.', C::ENTITYID_MAX_LENGTH),
        );

        new EntityDescriptor(
            entityId: str_pad('urn:x-simplesamlphp:', C::ENTITYID_MAX_LENGTH + 1, 'a'),
            affiliationDescriptor: new AffiliationDescriptor(C::ENTITY_IDP, [new AffiliateMember(C::ENTITY_OTHER)]),
        );
    }


    // test unmarshalling


    /**
     * Test creating an EntityDescriptor from XML.
     */
    public function testUnmarshalling(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $pdpd = $xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'PDPDescriptor')->item(0);
        $customd = $xmlRepresentation->createElementNS(C::NS_MD, 'md:RoleDescriptor');
        $customd->setAttribute('protocolSupportEnumeration', 'urn:oasis:names:tc:SAML:2.0:protocol');
        $customd->setAttributeNS(
            'http://www.w3.org/2000/xmlns/',
            'xmlns:ssp',
            'urn:x-simplesamlphp:namespace',
        );

        $type = new XMLAttribute(C::NS_XSI, 'xsi', 'type', 'ssp:UnknownRoleDescriptor');
        $type->toXML($customd);

        $newline = new DOMText("\n  ");
        /**
         * @psalm-suppress PossiblyNullPropertyFetch
         * @psalm-suppress PossiblyNullReference
         */
        $pdpd->parentNode->insertBefore($customd, $pdpd->nextSibling);
        $pdpd->parentNode->insertBefore($newline, $customd);
        $entityDescriptor = EntityDescriptor::fromXML($xmlRepresentation->documentElement);

        $attributes = $entityDescriptor->getAttributesNS();
        $this->assertCount(1, $attributes);

        $attribute = array_pop($attributes);
        $this->assertEquals(
            [
                'namespaceURI' => 'urn:test:something',
                'namespacePrefix' => 'test',
                'attrName' => 'attr1',
                'attrValue' => 'testval1',
            ],
            $attribute->toArray(),
        );
        $this->assertEquals(C::ENTITY_IDP, $entityDescriptor->getEntityID());
        $this->assertEquals('_5A3CHB081', $entityDescriptor->getID());
        $this->assertEquals('2020-02-05T09:39:25Z', $entityDescriptor->getValidUntil()->format(C::DATETIME_FORMAT));
        $this->assertEquals('P2Y6M5DT12H35M30S', $entityDescriptor->getCacheDuration());

        $roleDescriptors = $entityDescriptor->getRoleDescriptor();
        $this->assertCount(5, $roleDescriptors);
        $this->assertInstanceOf(IDPSSODescriptor::class, $roleDescriptors[0]);
        $this->assertInstanceOf(AttributeAuthorityDescriptor::class, $roleDescriptors[1]);
        $this->assertInstanceOf(AuthnAuthorityDescriptor::class, $roleDescriptors[2]);
        $this->assertInstanceOf(PDPDescriptor::class, $roleDescriptors[3]);

        $chunk = $roleDescriptors[4];
        $this->assertInstanceOf(UnknownRoleDescriptor::class, $chunk);
        $this->assertEquals('RoleDescriptor', $chunk->getLocalName());

        $this->assertInstanceOf(Organization::class, $entityDescriptor->getOrganization());

        $this->assertCount(3, $entityDescriptor->getContactPerson());
        $this->assertInstanceOf(ContactPerson::class, $entityDescriptor->getContactPerson()[0]);
        $this->assertInstanceOf(ContactPerson::class, $entityDescriptor->getContactPerson()[1]);
        $this->assertInstanceOf(ContactPerson::class, $entityDescriptor->getContactPerson()[2]);

        $this->assertCount(2, $entityDescriptor->getAdditionalMetadataLocation());
        $this->assertInstanceOf(
            AdditionalMetadataLocation::class,
            $entityDescriptor->getAdditionalMetadataLocation()[0],
        );
        $this->assertInstanceOf(
            AdditionalMetadataLocation::class,
            $entityDescriptor->getAdditionalMetadataLocation()[1],
        );

        $this->assertEquals(
            $xmlRepresentation->saveXML($xmlRepresentation->documentElement),
            strval($entityDescriptor),
        );
    }

    /**
     * Test that creating an EntityDescriptor from XML fails if no entityID is provided.
     */
    public function testUnmarshallingWithoutEntityId(): void
    {
        $entity_idp = C::ENTITY_IDP;
        $entity_sp = C::ENTITY_SP;

        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor affiliationOwnerID="{$entity_idp}">
        <AffiliateMember>{$entity_sp}</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'entityID\' attribute on md:EntityDescriptor.');
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an EntityDescriptor from XML fails if no RoleDescriptors are found.
     */
    public function testUnmarshallingWithoutDescriptors(): void
    {
        $entity_idp = C::ENTITY_IDP;
        $saml_md = C::NS_MD;

        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="{$entity_idp}" xmlns="{$saml_md}"></EntityDescriptor>
XML
        );
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'Must have either one of the RoleDescriptors or an AffiliationDescriptor in EntityDescriptor.',
        );
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an EntityDescriptor from XML fails with an invalid validUntil attribute.
     */
    public function testUnmarshallingWithInvalidValidUntil(): void
    {
        $entity_idp = C::ENTITY_IDP;
        $entity_sp = C::ENTITY_SP;

        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor validUntil="asdf" entityID="{$entity_idp}" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
    <AffiliationDescriptor affiliationOwnerID="{$entity_idp}">
        <AffiliateMember>{$entity_sp}</AffiliateMember>
    </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('\'asdf\' is not a valid DateTime');
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an EntityDescriptor from XML works when an AffiliationDescriptor is used.
     */
    public function testUnmarshallingWithAffiliationDescriptor(): void
    {
        $entity_idp = C::ENTITY_IDP;
        $entity_sp = C::ENTITY_SP;
        $entity_other = C::ENTITY_OTHER;

        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="{$entity_idp}" xmlns="urn:oasis:names:tc:SAML:2.0:metadata"
    validUntil="2010-02-01T12:34:56Z">
  <AffiliationDescriptor affiliationOwnerID="{$entity_idp}" ID="theAffiliationDescriptorID"
      validUntil="2010-02-01T12:34:56Z" cacheDuration="PT9000S">
    <AffiliateMember>{$entity_sp}</AffiliateMember>
    <AffiliateMember>{$entity_other}</AffiliateMember>
  </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $entityDescriptor = EntityDescriptor::fromXML($document->documentElement);
        $this->assertEquals([], $entityDescriptor->getRoleDescriptor());
        $this->assertInstanceOf(AffiliationDescriptor::class, $entityDescriptor->getAffiliationDescriptor());
    }


    /**
     * Test that creating an EntityDescriptor from XML fails when multiple AffiliationDescriptors are found.
     */
    public function testUnmarshallingWithSeveralAffiliationDescriptors(): void
    {
        $entity_idp = C::ENTITY_IDP;
        $entity_sp = C::ENTITY_SP;
        $entity_other = C::ENTITY_OTHER;

        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="{$entity_idp}" xmlns="urn:oasis:names:tc:SAML:2.0:metadata"
    validUntil="2010-02-01T12:34:56Z">
  <AffiliationDescriptor affiliationOwnerID="{$entity_idp}" ID="theAffiliationDescriptorID1"
      validUntil="2010-02-01T12:34:56Z" cacheDuration="PT9000S" >
    <AffiliateMember>{$entity_other}</AffiliateMember>
  </AffiliationDescriptor>
  <AffiliationDescriptor affiliationOwnerID="{$entity_idp}" ID="theAffiliationDescriptorID2"
      validUntil="2010-02-01T12:34:56Z" cacheDuration="PT9000S" >
    <AffiliateMember>{$entity_sp}</AffiliateMember>
  </AffiliationDescriptor>
</EntityDescriptor>
XML
        );
        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage('More than one AffiliationDescriptor in the entity.');
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an EntityDescriptor from XML fails if multiple Organization objects are included.
     */
    public function testUnmarshallingWithMultipleOrganizations(): void
    {
        $entity_idp = C::ENTITY_IDP;

        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="{$entity_idp}" ID="theID" validUntil="2010-01-01T12:34:56Z"
    cacheDuration="PT5000S" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
  <AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    <AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
        Location="https://idp.example.org/AttributeService" />
  </AttributeAuthorityDescriptor>
  <Organization>
    <OrganizationName xml:lang="en">orgNameTest (en)</OrganizationName>
    <OrganizationDisplayName xml:lang="en">orgDispNameTest (en)</OrganizationDisplayName>
    <OrganizationURL xml:lang="en">https://IdentityProvider.com</OrganizationURL>
  </Organization>
  <Organization>
    <OrganizationName xml:lang="no">orgNameTest (no)</OrganizationName>
    <OrganizationDisplayName xml:lang="no">orgDispNameTest (no)</OrganizationDisplayName>
    <OrganizationURL xml:lang="no">https://IdentityProvider.com</OrganizationURL>
  </Organization>
</EntityDescriptor>
XML
        );
        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage('More than one Organization in the entity.');
        EntityDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test that creating an EntityDescriptor from XML fails if both a RoleDescriptor and an AffiliationDescriptor
     * are included.
     */
    public function testUnmarshallingWithRoleandAffiliationDescriptors(): void
    {
        $entity_idp = C::ENTITY_IDP;
        $entity_other = C::ENTITY_OTHER;

        $document = DOMDocumentFactory::fromString(<<<XML
<EntityDescriptor entityID="{$entity_idp}" ID="theID" validUntil="2010-01-01T12:34:56Z"
    cacheDuration="PT5000S" xmlns="urn:oasis:names:tc:SAML:2.0:metadata">
  <AttributeAuthorityDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    <AttributeService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
        Location="https://idp.example.org/AttributeService" />
  </AttributeAuthorityDescriptor>
  <AffiliationDescriptor affiliationOwnerID="{$entity_idp}" ID="theAffiliationDescriptorID"
      validUntil="2010-02-01T12:34:56Z" cacheDuration="PT9000S" >
    <AffiliateMember>{$entity_other}</AffiliateMember>
  </AffiliationDescriptor>
  <Organization>
    <OrganizationName xml:lang="en">orgNameTest (en)</OrganizationName>
    <OrganizationDisplayName xml:lang="en">orgDispNameTest (en)</OrganizationDisplayName>
    <OrganizationURL xml:lang="en">https://IdentityProvider.com</OrganizationURL>
  </Organization>
</EntityDescriptor>
XML
        );
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'AffiliationDescriptor cannot be combined with other RoleDescriptor elements in EntityDescriptor.',
        );
        EntityDescriptor::fromXML($document->documentElement);
    }
}
