<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\md\{
    AbstractMdElement,
    AbstractMetadataDocument,
    AbstractSignedMdElement,
    EntitiesDescriptor,
    EntityDescriptor,
    Extensions,
};
use SimpleSAML\SAML2\XML\mdrpi\{PublicationInfo, UsagePolicy};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XMLSchema\Type\{IDValue, LanguageValue};
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for the md:EntitiesDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(EntitiesDescriptor::class)]
#[CoversClass(AbstractSignedMdElement::class)]
#[CoversClass(AbstractMetadataDocument::class)]
#[CoversClass(AbstractMdElement::class)]
final class EntitiesDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = EntitiesDescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_EntitiesDescriptor.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an EntitiesDescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $extensions = new Extensions([
            new PublicationInfo(
                publisher: SAMLStringValue::fromString('http://publisher.ra/'),
                creationInstant: SAMLDateTimeValue::fromString('2020-02-03T13:46:24Z'),
                usagePolicy: [
                    new UsagePolicy(
                        LanguageValue::fromString('en'),
                        SAMLAnyURIValue::fromString('http://publisher.ra/policy.txt'),
                    ),
                ],
            ),
        ]);
        $entitiesdChildElement = self::$xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntitiesDescriptor',
        );
        $entitydElement = self::$xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntityDescriptor',
        );

        /** @psalm-suppress PossiblyNullArgument */
        $childEntitiesd = EntitiesDescriptor::fromXML($entitiesdChildElement->item(0));

        /** @psalm-suppress PossiblyNullArgument */
        $childEntityd = EntityDescriptor::fromXML($entitydElement->item(1));

        $entitiesd = new EntitiesDescriptor(
            entityDescriptors: [$childEntityd],
            entitiesDescriptors: [$childEntitiesd],
            Name: SAMLStringValue::fromString('Federation'),
            extensions: $extensions,
            ID: IDValue::fromString('phpunit'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($entitiesd),
        );
    }


    /**
     * Test that creating an EntitiesDescriptor from scratch with no Name works.
     */
    public function testMarshallingWithNoName(): void
    {
        $entitydElement = self::$xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntityDescriptor',
        );
        /** @psalm-suppress PossiblyNullArgument */
        $childEntityd = EntityDescriptor::fromXML($entitydElement->item(1));
        $entitiesd = new EntitiesDescriptor([$childEntityd]);
        $this->assertNull($entitiesd->getName());
        $this->assertEmpty($entitiesd->getEntitiesDescriptors());
    }


    /**
     * Test that creating an EntitiesDescriptor from scratch with only a nested EntitiesDescriptor works.
     */
    public function testMarshallingWithOnlyEntitiesDescriptor(): void
    {
        $entitiesdChildElement = self::$xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntitiesDescriptor',
        );
        /** @psalm-suppress PossiblyNullArgument */
        $childEntitiesd = EntitiesDescriptor::fromXML($entitiesdChildElement->item(0));
        $entitiesd = new EntitiesDescriptor(
            [],
            [$childEntitiesd],
        );
        $this->assertEmpty($entitiesd->getEntityDescriptors());
    }


    /**
     * Test that creating an empty EntitiesDescriptor from scratch fails.
     */
    public function testMarshallingEmpty(): void
    {
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'At least one md:EntityDescriptor or md:EntitiesDescriptor element is required.',
        );
        new EntitiesDescriptor();
    }


    // test unmarshalling


    /**
     * Test that creating an EntitiesDescriptor without Name from XML works.
     */
    public function testUnmarshallingWithoutName(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->removeAttribute('Name');
        $entitiesd = EntitiesDescriptor::fromXML($xmlRepresentation->documentElement);
        $this->assertNull($entitiesd->getName());
    }


    /**
     * Test that creating an EntitiesDescriptor without nested EntitiesDescriptor elements from XML works.
     */
    public function testUnmarshallingWithoutEntities(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $entities = $xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntitiesDescriptor',
        );
        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($entities->item(0));
        $entitiesd = EntitiesDescriptor::fromXML($xmlRepresentation->documentElement);
        $this->assertEquals([], $entitiesd->getEntitiesDescriptors());
        $this->assertCount(1, $entitiesd->getEntityDescriptors());
    }


    /**
     * Test that creating an EntitiesDescriptor from XML without any EntityDescriptor works.
     */
    public function testUnmarshallingWithoutEntity(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $entity = $xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntityDescriptor',
        );
        /*
         *  getElementsByTagNameNS() searches recursively. Therefore, it finds first the EntityDescriptor that's
         *  inside the nested EntitiesDescriptor. We then need to fetch the second result of the search, which will be
         *  the child of the parent EntitiesDescriptor.
         */

        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($entity->item(1));
        $entitiesd = EntitiesDescriptor::fromXML($xmlRepresentation->documentElement);
        $this->assertEquals([], $entitiesd->getEntityDescriptors());
        $this->assertCount(1, $entitiesd->getEntitiesDescriptors());
    }


    /**
     * Test that creating an empty EntitiesDescriptor from XML fails.
     */
    public function testUnmarshallingEmpty(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        // remove child EntitiesDescriptor
        $entities = $xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntitiesDescriptor',
        );
        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($entities->item(0));

        // remove child EntityDescriptor
        $entity = $xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntityDescriptor',
        );
        /** @psalm-suppress PossiblyNullArgument */
        $xmlRepresentation->documentElement->removeChild($entity->item(0));

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'At least one md:EntityDescriptor or md:EntitiesDescriptor element is required.',
        );
        EntitiesDescriptor::fromXML($xmlRepresentation->documentElement);
    }
}
