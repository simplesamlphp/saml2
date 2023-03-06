<?php

declare(strict_types=1);

namespace SAML2\XML;

use SAML2\DOMDocumentFactory;
use SAML2\XML\saml\Attribute;
use SAML2\XML\saml\AttributeValue;
use SAML2\XML\Chunk;

/**
 * Class \SAML2\XML\ChunkTest
 */
class ChunkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \SAML2\XML\Chunk
     */
    private $chunk;


    /**
     * Make a new Chunk object to test with
     * @return void
     */
    public function setUp(): void
    {
        $attribute = new Attribute();
        $attribute->setName('TheName');
        $attribute->setNameFormat('TheNameFormat');
        $attribute->setFriendlyName('TheFriendlyName');
        $attribute->setAttributeValue([
            new AttributeValue('FirstValue'),
            new AttributeValue('SecondValue'),
        ]);

        $document = DOMDocumentFactory::fromString('<root />');
        $attributeElement = $attribute->toXML($document->firstChild);

        $this->chunk = new Chunk($attributeElement);
    }


    /**
     * Test serialization and unserialization
     * @return void
     */
    public function testChunkSerializationLoop(): void
    {
        $ser = $this->chunk->serialize();
        $document = DOMDocumentFactory::fromString('<root />');
        $newchunk = new Chunk($document->firstChild);
        $newchunk->unserialize($ser);

        $this->assertEqualXMLStructure($this->chunk->getXML(), $newchunk->getXML());
    }
}
