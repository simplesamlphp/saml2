<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\XML\Chunk;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\shibmd\Scope;

/**
 * Class \SAML2\XML\samlp\ExtensionsTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\Extensions
 * @package simplesamlphp/saml2
 */
final class ExtensionsTest extends TestCase
{
    /**
     * @var \DOMDocument
     */
    private $document;


    /**
     * Prepare a basic DOMElement to test against
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:Extensions xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol">
  <myns:AttributeList xmlns:myns="urn:mynamespace">
    <myns:Attribute name="UserName" value=""/>
  </myns:AttributeList>
  <ExampleElement name="AnotherExtension" />
</samlp:Extensions>
XML
        );
    }


    /**
     * Test the getList() method.
     * @return void
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
     * @return void
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
     * @return void
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
