<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\AssertionConsumerService;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\md\AssertionConsumerServiceTest
 *
 * @covers \SimpleSAML\SAML2\XML\md\AssertionConsumerService
 * @covers \SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class AssertionConsumerServiceTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \SimpleSAML\XML\Chunk */
    protected Chunk $ext;

    /** @var \SimpleSAML\XML\Attribute */
    protected XMLAttribute $attr;


    /**
     */
    protected function setUp(): void
    {
        $this->ext = new Chunk(DOMDocumentFactory::fromString(
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>',
        )->documentElement);

        $this->attr = new XMLAttribute('urn:x-simplesamlphp:namespace', 'ssp', 'attr1', 'testval1');

        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = AssertionConsumerService::class;

        $this->arrayRepresentation = [
            'index' => 1,
            'Binding' => C::BINDING_HTTP_POST,
            'Location' => 'https://whatever/',
            'isDefault' => true,
            'ResponseLocation' => 'https://foo.bar/',
            'Extensions' => [$this->ext],
            'attributes' => [$this->attr->toArray()],
        ];

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AssertionConsumerService.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an IndexedEndpointType from scratch.
     */
    public function testMarshalling(): void
    {
        $idxep = new AssertionConsumerService(
            42,
            C::BINDING_HTTP_POST,
            'https://simplesamlphp.org/some/endpoint',
            false,
            'https://foo.bar/',
            [$this->attr],
            [$this->ext],
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($idxep),
        );
    }


    // test unmarshalling


    /**
     * Test creating an IndexedEndpointType from XML.
     */
    public function testUnmarshalling(): void
    {
        $idxep = AssertionConsumerService::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($idxep),
        );
    }
}
