<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;
use SAML2\XML\saml\Attribute;
use SAML2\XML\shibmd\Scope;

/**
 * Class \SAML2\XML\samlp\ExtensionsTest
 */
class ExtensionsTest extends TestCase
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
        $this->markTestSkipped();
        $list = Extensions::getList($this->document->documentElement);

        $this->assertCount(2, $list);
        $this->assertEquals("urn:mynamespace", $list[0]->getNamespaceURI());
        $this->assertEquals("ExampleElement", $list[1]->getLocalName());
    }


    /**
     * Adding empty list should leave existing extensions unchanged.
     * @return void
     */
    public function testExtensionsAddEmpty(): void
    {
        $this->markTestSkipped();
        Extensions::addList($this->document->documentElement, []);

        $list = Extensions::getList($this->document->documentElement);

        $this->assertCount(2, $list);
        $this->assertEquals("urn:mynamespace", $list[0]->getNamespaceURI());
        $this->assertEquals("ExampleElement", $list[1]->getLocalName());
    }


    /**
     * Test adding two random elements.
     * @return void
     */
    public function testExtensionsAddSome(): void
    {
        $this->markTestSkipped();
        $attribute = new Attribute('TheName');
        $scope = new Scope("scope");

        $extensions = new Extensions([
            new Chunk($attribute->toXML()),
            new Chunk($scope->toXML()),
        ]);
        $list = $extensions->getList();

        $this->assertCount(4, $list);
        $this->assertEquals("urn:mynamespace", $list[0]->getNamespaceURI());
        $this->assertEquals("ExampleElement", $list[1]->getLocalName());
        $this->assertEquals("Attribute", $list[2]->getLocalName());
        $this->assertEquals("urn:mace:shibboleth:metadata:1.0", $list[3]->getNamespaceURI());
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
