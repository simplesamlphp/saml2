<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\SignedElementTestTrait;
use SAML2\Utils;
use SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Tests for the md:EntitiesDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
final class EntitiesDescriptorTest extends TestCase
{
    use SignedElementTestTrait;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $dsns = XMLSecurityDSig::XMLDSIGNS;
        $samlns = Constants::NS_SAML;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:EntitiesDescriptor xmlns:md="{$mdns}" Name="Federation">
  <md:Extensions>
    <mdrpi:PublicationInfo xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi" publisher="http://publisher.ra/" creationInstant="2020-02-03T13:46:24Z">
      <mdrpi:UsagePolicy xml:lang="en">http://publisher.ra/policy.txt</mdrpi:UsagePolicy>
    </mdrpi:PublicationInfo>
  </md:Extensions>
  <md:EntitiesDescriptor Name="subfederation">
    <md:EntityDescriptor entityID="https://ServiceProvider.com/SAML">
      <md:SPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" AuthnRequestsSigned="true">
        <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact" Location="https://ServiceProvider.com/SAML/SSO/Artifact" index="0" isDefault="true"/>
      </md:SPSSODescriptor>
      <md:Organization>
        <md:OrganizationName xml:lang="en">Academic Journals R US</md:OrganizationName>
        <md:OrganizationDisplayName xml:lang="en">Academic Journals R US, a Division of Dirk Corp.</md:OrganizationDisplayName>
        <md:OrganizationURL xml:lang="en">https://ServiceProvider.com</md:OrganizationURL>
      </md:Organization>
    </md:EntityDescriptor>
  </md:EntitiesDescriptor>
  <md:EntityDescriptor entityID="https://IdentityProvider.com/SAML">
    <md:IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" WantAuthnRequestsSigned="true">
      <md:KeyDescriptor use="signing">
        <ds:KeyInfo xmlns:ds="{$dsns}">
          <ds:KeyName>IdentityProvider.com SSO Key</ds:KeyName>
        </ds:KeyInfo>
      </md:KeyDescriptor>
      <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP" Location="https://IdentityProvider.com/SAML/SLO/SOAP"/>
      <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://IdentityProvider.com/SAML/SLO/Browser" ResponseLocation="https://IdentityProvider.com/SAML/SLO/Response"/>
      <md:NameIDFormat>urn:oasis:names:tc:SAML:1.1:nameid-format:X509SubjectName</md:NameIDFormat>
      <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:persistent</md:NameIDFormat>
      <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:transient</md:NameIDFormat>
      <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="https://IdentityProvider.com/SAML/SSO/Browser"/>
      <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" Location="https://IdentityProvider.com/SAML/SSO/Browser"/>
      <saml:Attribute xmlns:saml="{$samlns}" Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.6" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri" FriendlyName="eduPersonPrincipalName"></saml:Attribute>
      <saml:Attribute xmlns:saml="{$samlns}" Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.1" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri" FriendlyName="eduPersonAffiliation">
        <saml:AttributeValue>member</saml:AttributeValue>
        <saml:AttributeValue>student</saml:AttributeValue>
        <saml:AttributeValue>faculty</saml:AttributeValue>
        <saml:AttributeValue>employee</saml:AttributeValue>
        <saml:AttributeValue>staff</saml:AttributeValue>
      </saml:Attribute>
    </md:IDPSSODescriptor>
    <md:Organization>
      <md:OrganizationName xml:lang="en">Identity Providers R US</md:OrganizationName>
      <md:OrganizationDisplayName xml:lang="en">Identity Providers R US, a Division of Lerxst Corp.</md:OrganizationDisplayName>
      <md:OrganizationURL xml:lang="en">https://IdentityProvider.com</md:OrganizationURL>
    </md:Organization>
  </md:EntityDescriptor>
</md:EntitiesDescriptor>
XML
        );

        $this->testedClass = EntitiesDescriptor::class;
    }


    // test marshalling


    /**
     * Test creating an EntitiesDescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $extensions = new Extensions(
            [
                new PublicationInfo(
                    'http://publisher.ra/',
                    Utils::xsDateTimeToTimestamp('2020-02-03T13:46:24Z'),
                    null,
                    ['en' => 'http://publisher.ra/policy.txt']
                )
            ]
        );
        $entitiesdChildElement = $this->document->documentElement->getElementsByTagNameNS(
            Constants::NS_MD,
            'EntitiesDescriptor'
        );
        $entitydElement = $this->document->documentElement->getElementsByTagNameNS(
            Constants::NS_MD,
            'EntityDescriptor'
        );

        /** @psalm-suppress PossiblyNullArgument */
        $childEntitiesd = EntitiesDescriptor::fromXML($entitiesdChildElement->item(0));

        /** @psalm-suppress PossiblyNullArgument */
        $childEntityd = EntityDescriptor::fromXML($entitydElement->item(1));

        $entitiesd = new EntitiesDescriptor(
            [$childEntityd],
            [$childEntitiesd],
            'Federation',
            null,
            null,
            null,
            $extensions
        );

        $this->assertInstanceOf(Extensions::class, $entitiesd->getExtensions());
        $this->assertCount(1, $entitiesd->getEntitiesDescriptors());
        $this->assertInstanceOf(EntitiesDescriptor::class, $entitiesd->getEntitiesDescriptors()[0]);
        $this->assertCount(1, $entitiesd->getEntityDescriptors());
        $this->assertInstanceOf(EntityDescriptor::class, $entitiesd->getEntityDescriptors()[0]);

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($entitiesd)
        );
    }


    /**
     * Test that creating an EntitiesDescriptor from scratch with no Name works.
     */
    public function testMarshallingWithNoName(): void
    {
        $entitydElement = $this->document->documentElement->getElementsByTagNameNS(
            Constants::NS_MD,
            'EntityDescriptor'
        );
        /** @psalm-suppress PossiblyNullArgument */
        $childEntityd = EntityDescriptor::fromXML($entitydElement->item(1));
        $entitiesd = new EntitiesDescriptor(
            [$childEntityd]
        );
        $this->assertNull($entitiesd->getName());
        $this->assertIsArray($entitiesd->getEntitiesDescriptors());
        $this->assertEmpty($entitiesd->getEntitiesDescriptors());
    }


    /**
     * Test that creating an EntitiesDescriptor from scratch with only a nested EntitiesDescriptor works.
     */
    public function testMarshallingWithOnlyEntitiesDescriptor(): void
    {
        $entitiesdChildElement = $this->document->documentElement->getElementsByTagNameNS(
            Constants::NS_MD,
            'EntitiesDescriptor'
        );
        /** @psalm-suppress PossiblyNullArgument */
        $childEntitiesd = EntitiesDescriptor::fromXML($entitiesdChildElement->item(0));
        $entitiesd = new EntitiesDescriptor(
            [],
            [$childEntitiesd]
        );
        $this->assertIsArray($entitiesd->getEntityDescriptors());
        $this->assertEmpty($entitiesd->getEntityDescriptors());
    }


    /**
     * Test that creating an empty EntitiesDescriptor from scratch fails.
     */
    public function testMarshallingEmpty(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'At least one md:EntityDescriptor or md:EntitiesDescriptor element is required.'
        );
        new EntitiesDescriptor();
    }


    // test unmarshalling


    /**
     * Test creating an EntitiesDescriptor from XML.
     */
    public function testUnmarshalling(): void
    {
        $entitiesd = EntitiesDescriptor::fromXML($this->document->documentElement);
        $this->assertEquals('Federation', $entitiesd->getName());
        $this->assertInstanceOf(Extensions::class, $entitiesd->getExtensions());
        $this->assertCount(1, $entitiesd->getEntitiesDescriptors());
        $this->assertInstanceOf(EntitiesDescriptor::class, $entitiesd->getEntitiesDescriptors()[0]);
        $this->assertCount(1, $entitiesd->getEntityDescriptors());
        $this->assertInstanceOf(EntityDescriptor::class, $entitiesd->getEntityDescriptors()[0]);
    }


    /**
     * Test that creating an EntitiesDescriptor without Name from XML works.
     */
    public function testUnmarshallingWithoutName(): void
    {
        $this->document->documentElement->removeAttribute('Name');
        $entitiesd = EntitiesDescriptor::fromXML($this->document->documentElement);
        $this->assertNull($entitiesd->getName());
    }


    /**
     * Test that creating an EntitiesDescriptor with an empty Name from XML works.
     */
    public function testUnmarshallingWithEmptyName(): void
    {
        $this->document->documentElement->setAttribute('Name', '');
        $entitiesd = EntitiesDescriptor::fromXML($this->document->documentElement);
        $this->assertEquals('', $entitiesd->getName());
    }


    /**
     * Test that creating an EntitiesDescriptor without nested EntitiesDescriptor elements from XML works.
     */
    public function testUnmarshallingWithoutEntities(): void
    {
        $entities = $this->document->documentElement->getElementsByTagNameNS(Constants::NS_MD, 'EntitiesDescriptor');
        /** @psalm-suppress PossiblyNullArgument */
        $this->document->documentElement->removeChild($entities->item(0));
        $entitiesd = EntitiesDescriptor::fromXML($this->document->documentElement);
        $this->assertEquals([], $entitiesd->getEntitiesDescriptors());
        $this->assertCount(1, $entitiesd->getEntityDescriptors());
    }


    /**
     * Test that creating an EntitiesDescriptor from XML without any EntityDescriptor works.
     */
    public function testUnmarshallingWithoutEntity(): void
    {
        $entity = $this->document->documentElement->getElementsByTagNameNS(Constants::NS_MD, 'EntityDescriptor');
        /*
         *  getElementsByTagNameNS() searches recursively. Therefore, it finds first the EntityDescriptor that's
         * inside the nested EntitiesDescriptor. We then need to fetch the second result of the search, which will be
         *  the child of the parent EntitiesDescriptor.
         */

        /** @psalm-suppress PossiblyNullArgument */
        $this->document->documentElement->removeChild($entity->item(1));
        $entitiesd = EntitiesDescriptor::fromXML($this->document->documentElement);
        $this->assertEquals([], $entitiesd->getEntityDescriptors());
        $this->assertCount(1, $entitiesd->getEntitiesDescriptors());
    }


    /**
     * Test that creating an empty EntitiesDescriptor from XML fails.
     */
    public function testUnmarshallingEmpty(): void
    {
        // remove child EntitiesDescriptor
        $entities = $this->document->documentElement->getElementsByTagNameNS(Constants::NS_MD, 'EntitiesDescriptor');
        /** @psalm-suppress PossiblyNullArgument */
        $this->document->documentElement->removeChild($entities->item(0));

        // remove child EntityDescriptor
        $entity = $this->document->documentElement->getElementsByTagNameNS(Constants::NS_MD, 'EntityDescriptor');
        /** @psalm-suppress PossiblyNullArgument */
        $this->document->documentElement->removeChild($entity->item(0));

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'At least one md:EntityDescriptor or md:EntitiesDescriptor element is required.'
        );
        EntitiesDescriptor::fromXML($this->document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(EntitiesDescriptor::fromXML($this->document->documentElement))))
        );
    }
}
