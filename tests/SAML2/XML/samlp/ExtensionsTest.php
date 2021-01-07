<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\shibmd\Scope;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Chunk;

/**
 * Class \SAML2\XML\samlp\ExtensionsTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\Extensions
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class ExtensionsTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     * Prepare a basic DOMElement to test against
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_Extensions.xml'
        );
    }


    /**
     * Test the getList() method.
     */
    public function testExtensionsGet(): void
    {
        $extensions = Extensions::fromXML($this->document->documentElement);
        $list = $extensions->getList();

        $this->assertCount(2, $list);
        $this->assertInstanceOf(Chunk::class, $list[0]);
        $this->assertInstanceOf(Chunk::class, $list[1]);
        $this->assertEquals("urn:mynamespace", $list[0]->getNamespaceURI());
        $this->assertEquals("ExampleElement", $list[1]->getLocalName());
    }


    /**
     * Adding empty list should leave existing extensions unchanged.
     */
    public function testExtensionsAddEmpty(): void
    {
        $extensions = Extensions::fromXML($this->document->documentElement);

        $list = $extensions->getList();

        $this->assertCount(2, $list);
        $this->assertInstanceOf(Chunk::class, $list[0]);
        $this->assertInstanceOf(Chunk::class, $list[1]);
        $this->assertEquals("urn:mynamespace", $list[0]->getNamespaceURI());
        $this->assertEquals("ExampleElement", $list[1]->getLocalName());
    }


    /**
     * Test adding two random elements.
     */
    public function testExtensionsAddSome(): void
    {
        $attribute = new Attribute('TheName');
        $scope = new Scope("scope");

        $extensions = new Extensions([
            new Chunk($attribute->toXML()),
            new Chunk($scope->toXML()),
        ]);
        $list = $extensions->getList();

        $this->assertCount(2, $list);
        $this->assertInstanceOf(Chunk::class, $list[0]);
        $this->assertInstanceOf(Chunk::class, $list[1]);
        $this->assertEquals("Attribute", $list[0]->getLocalName());
        $this->assertEquals("urn:mace:shibboleth:metadata:1.0", $list[1]->getNamespaceURI());
    }


    /**
     * Test that serialization / unserialization works.
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Extensions::fromXML($this->document->documentElement))))
        );
    }
}
