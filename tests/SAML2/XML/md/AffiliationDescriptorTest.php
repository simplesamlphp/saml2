<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use Exception;
use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\{SAMLDateTimeValue, EntityIDValue, KeyTypesValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\md\{
    AbstractMdElement,
    AbstractMetadataDocument,
    AbstractSignedMdElement,
    AffiliateMember,
    AffiliationDescriptor,
    KeyDescriptor,
    KeyTypesEnum,
};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\{DurationValue, IDValue};
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;
use SimpleSAML\XMLSecurity\XML\ds\{KeyInfo, KeyName};

use function dirname;
use function strval;

/**
 * Tests for the AffiliationDescriptor class.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(AffiliationDescriptor::class)]
#[CoversClass(AbstractSignedMdElement::class)]
#[CoversClass(AbstractMetadataDocument::class)]
#[CoversClass(AbstractMdElement::class)]
final class AffiliationDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AffiliationDescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AffiliationDescriptor.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an AffiliationDescriptor object from scratch.
     */
    public function testMarshalling(): void
    {
        $affiliationDescriptor = new AffiliationDescriptor(
            affiliationOwnerId: EntityIDValue::fromString(C::ENTITY_IDP),
            affiliateMember: [
                new AffiliateMember(
                    EntityIDValue::fromString(C::ENTITY_SP),
                ),
                new AffiliateMember(
                    EntityIDValue::fromString(C::ENTITY_OTHER),
                ),
            ],
            ID: IDValue::fromString('TheID'),
            validUntil: SAMLDateTimeValue::fromString('2009-02-13T23:31:30Z'),
            cacheDuration: DurationValue::fromString('PT5000S'),
            keyDescriptor: [
                new KeyDescriptor(
                    new KeyInfo(
                        [
                            new KeyName(
                                SAMLStringValue::fromString('IdentityProvider.com SSO Key'),
                            ),
                        ],
                    ),
                    KeyTypesValue::fromEnum(KeyTypesEnum::SIGNING),
                ),
            ],
            namespacedAttribute: [
                new XMLAttribute(C::NAMESPACE, 'ssp', 'attr1', SAMLStringValue::fromString('value1')),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($affiliationDescriptor),
        );
    }


    /**
     * Test that creating an AffiliationDescriptor with an empty list of members fails.
     */
    public function testMarshallingWithEmptyMemberList(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('List of affiliated members must not be empty.');
        new AffiliationDescriptor(
            affiliationOwnerId: EntityIDValue::fromString(C::ENTITY_IDP),
            affiliateMember: [],
        );
    }


    // test unmarshalling


    /**
     * Test failure to create an AffiliationDescriptor from XML when there's no affiliation members.
     */
    public function testUnmarshallingWithoutMembers(): void
    {
        $mdNamespace = AffiliationDescriptor::NS;
        $entity_idp = C::ENTITY_IDP;

        $document = DOMDocumentFactory::fromString(
            <<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" affiliationOwnerID="{$entity_idp}" ID="TheID"
    validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
</md:AffiliationDescriptor>
XML
            ,
        );
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('List of affiliated members must not be empty.');
        AffiliationDescriptor::fromXML($document->documentElement);
    }


    /**
     * Test failure to create an AffiliationDescriptor from XML when there's no owner specified.
     */
    public function testUnmarshallingWithoutOwner(): void
    {
        $mdNamespace = AffiliationDescriptor::NS;
        $entity_other = C::ENTITY_OTHER;
        $entity_sp = C::ENTITY_SP;

        $document = DOMDocumentFactory::fromString(
            <<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" ID="TheID"
    validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
  <md:AffiliateMember>{$entity_sp}</md:AffiliateMember>
  <md:AffiliateMember>{$entity_other}</md:AffiliateMember>
</md:AffiliationDescriptor>
XML
            ,
        );

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'affiliationOwnerID' attribute on md:AffiliationDescriptor.");
        AffiliationDescriptor::fromXML($document->documentElement);
    }
}
