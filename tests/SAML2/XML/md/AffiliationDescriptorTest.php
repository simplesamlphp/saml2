<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\md\AffiliateMember;
use SimpleSAML\SAML2\XML\md\AffiliationDescriptor;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;

use function dirname;
use function strval;

/**
 * Tests for the AffiliationDescriptor class.
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @covers \SimpleSAML\SAML2\XML\md\AbstractSignedMdElement
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMetadataDocument
 * @covers \SimpleSAML\SAML2\XML\md\AffiliationDescriptor
 * @package simplesamlphp/saml2
 */
final class AffiliationDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = AffiliationDescriptor::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
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
            affiliationOwnerId: C::ENTITY_IDP,
            affiliateMember: [new AffiliateMember(C::ENTITY_SP), new AffiliateMember(C::ENTITY_OTHER)],
            ID: 'TheID',
            validUntil: 1234567890,
            cacheDuration: 'PT5000S',
            keyDescriptor: [
                new KeyDescriptor(
                    new KeyInfo(
                        [
                            new KeyName('IdentityProvider.com SSO Key'),
                        ],
                    ),
                    'signing',
                ),
            ],
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($affiliationDescriptor),
        );
    }


    /**
     * Test that creating an AffiliationDescriptor with an empty owner ID fails.
     */
    public function testMarhsallingWithEmptyOwnerID(): void
    {
        $this->expectException(SchemaViolationException::class);
        new AffiliationDescriptor(
            affiliationOwnerId: '',
            affiliateMember: [new AffiliateMember(C::ENTITY_SP), new AffiliateMember(C::ENTITY_OTHER)],
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
            affiliationOwnerId: C::ENTITY_IDP,
            affiliateMember: [],
        );
    }


    // test unmarshalling


    /**
     * Test creating an AffiliationDescriptor from XML.
     */
    public function testUnmarshalling(): void
    {
        $affiliationDescriptor = AffiliationDescriptor::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($affiliationDescriptor),
        );
    }


    /**
     * Test failure to create an AffiliationDescriptor from XML when there's no affiliation members.
     */
    public function testUnmarshallingWithoutMembers(): void
    {
        $mdNamespace = AffiliationDescriptor::NS;
        $entity_idp = C::ENTITY_IDP;

        $document = DOMDocumentFactory::fromString(<<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" affiliationOwnerID="{$entity_idp}" ID="TheID"
    validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
</md:AffiliationDescriptor>
XML
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

        $document = DOMDocumentFactory::fromString(<<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" ID="TheID"
    validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
  <md:AffiliateMember>{$entity_sp}</md:AffiliateMember>
  <md:AffiliateMember>{$entity_other}</md:AffiliateMember>
</md:AffiliationDescriptor>
XML
        );

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'affiliationOwnerID' attribute on md:AffiliationDescriptor.");
        AffiliationDescriptor::fromXML($document->documentElement);
    }
}
