<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\EntityDescriptor;
use SimpleSAML\SAML2\XML\md\EntitiesDescriptor;
use SimpleSAML\SAML2\XML\md\Extensions;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\UsagePolicy;
use SimpleSAML\Test\SAML2\SignedElementTestTrait;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XMLSecurityDSig;

use function dirname;
use function strval;

/**
 * Tests for the md:EntitiesDescriptor element.
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @covers \SimpleSAML\SAML2\XML\md\EntitiesDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMetadataDocument
 * @package simplesamlphp/saml2
 */
final class EntitiesDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = EntitiesDescriptor::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_EntitiesDescriptor.xml'
        );
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
                    XMLUtils::xsDateTimeToTimestamp('2020-02-03T13:46:24Z'),
                    null,
                    [new UsagePolicy('en', 'http://publisher.ra/policy.txt')]
                )
            ]
        );
        $entitiesdChildElement = $this->xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntitiesDescriptor'
        );
        $entitydElement = $this->xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
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

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($entitiesd)
        );
    }


    /**
     * Test that creating an EntitiesDescriptor from scratch with no Name works.
     */
    public function testMarshallingWithNoName(): void
    {
        $entitydElement = $this->xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntityDescriptor'
        );
        /** @psalm-suppress PossiblyNullArgument */
        $childEntityd = EntityDescriptor::fromXML($entitydElement->item(1));
        $entitiesd = new EntitiesDescriptor(
            [$childEntityd]
        );
        $this->assertNull($entitiesd->getName());
        $this->assertEmpty($entitiesd->getEntitiesDescriptors());
    }


    /**
     * Test that creating an EntitiesDescriptor from scratch with only a nested EntitiesDescriptor works.
     */
    public function testMarshallingWithOnlyEntitiesDescriptor(): void
    {
        $entitiesdChildElement = $this->xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntitiesDescriptor'
        );
        /** @psalm-suppress PossiblyNullArgument */
        $childEntitiesd = EntitiesDescriptor::fromXML($entitiesdChildElement->item(0));
        $entitiesd = new EntitiesDescriptor(
            [],
            [$childEntitiesd]
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
        $entitiesd = EntitiesDescriptor::fromXML($this->xmlRepresentation->documentElement);
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
        $this->xmlRepresentation->documentElement->removeAttribute('Name');
        $entitiesd = EntitiesDescriptor::fromXML($this->xmlRepresentation->documentElement);
        $this->assertNull($entitiesd->getName());
    }


    /**
     * Test that creating an EntitiesDescriptor with an empty Name from XML works.
     */
    public function testUnmarshallingWithEmptyName(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('Name', '');
        $entitiesd = EntitiesDescriptor::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals('', $entitiesd->getName());
    }


    /**
     * Test that creating an EntitiesDescriptor without nested EntitiesDescriptor elements from XML works.
     */
    public function testUnmarshallingWithoutEntities(): void
    {
        $entities = $this->xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntitiesDescriptor'
        );
        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($entities->item(0));
        $entitiesd = EntitiesDescriptor::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals([], $entitiesd->getEntitiesDescriptors());
        $this->assertCount(1, $entitiesd->getEntityDescriptors());
    }


    /**
     * Test that creating an EntitiesDescriptor from XML without any EntityDescriptor works.
     */
    public function testUnmarshallingWithoutEntity(): void
    {
        $entity = $this->xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntityDescriptor'
        );
        /*
         *  getElementsByTagNameNS() searches recursively. Therefore, it finds first the EntityDescriptor that's
         *  inside the nested EntitiesDescriptor. We then need to fetch the second result of the search, which will be
         *  the child of the parent EntitiesDescriptor.
         */

        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($entity->item(1));
        $entitiesd = EntitiesDescriptor::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals([], $entitiesd->getEntityDescriptors());
        $this->assertCount(1, $entitiesd->getEntitiesDescriptors());
    }


    /**
     * Test that creating an empty EntitiesDescriptor from XML fails.
     */
    public function testUnmarshallingEmpty(): void
    {
        // remove child EntitiesDescriptor
        $entities = $this->xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntitiesDescriptor'
        );
        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($entities->item(0));

        // remove child EntityDescriptor
        $entity = $this->xmlRepresentation->documentElement->getElementsByTagNameNS(
            C::NS_MD,
            'EntityDescriptor'
        );
        /** @psalm-suppress PossiblyNullArgument */
        $this->xmlRepresentation->documentElement->removeChild($entity->item(0));

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'At least one md:EntityDescriptor or md:EntitiesDescriptor element is required.'
        );
        EntitiesDescriptor::fromXML($this->xmlRepresentation->documentElement);
    }
}
