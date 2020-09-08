<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\SignedElementTestTrait;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XMLSecurityDSig;

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
    use SignedElementTestTrait;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_EntitiesDescriptor.xml'
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
                    XMLUtils::xsDateTimeToTimestamp('2020-02-03T13:46:24Z'),
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
