<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\samlp\Extensions;
use SimpleSAML\SAML2\XML\shibmd\Scope;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\samlp\ExtensionsTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\Extensions
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class ExtensionsTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     * Prepare a basic DOMElement to test against
     */
    public function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        $this->testedClass = Extensions::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_Extensions.xml',
        );
    }


    /**
     * Test the getList() method.
     */
    public function testExtensionsGet(): void
    {
        $extensions = Extensions::fromXML($this->xmlRepresentation->documentElement);
        $list = $extensions->getList();

        $this->assertCount(2, $list);
        $this->assertInstanceOf(Chunk::class, $list[0]);
        $this->assertInstanceOf(Chunk::class, $list[1]);
        $this->assertEquals("urn:test:mynamespace", $list[0]->getNamespaceURI());
        $this->assertEquals("ExampleElement", $list[1]->getLocalName());
    }


    /**
     * Adding empty list should leave existing extensions unchanged.
     */
    public function testExtensionsAddEmpty(): void
    {
        $extensions = Extensions::fromXML($this->xmlRepresentation->documentElement);

        $list = $extensions->getList();

        $this->assertCount(2, $list);
        $this->assertInstanceOf(Chunk::class, $list[0]);
        $this->assertInstanceOf(Chunk::class, $list[1]);
        $this->assertEquals("urn:test:mynamespace", $list[0]->getNamespaceURI());
        $this->assertEquals("ExampleElement", $list[1]->getLocalName());
    }


    /**
     * Test adding two random elements.
     */
    public function testExtensionsAddSome(): void
    {
        $scope = new Scope("scope");

        $extensions = new Extensions([
            new Chunk($scope->toXML()),
        ]);
        $list = $extensions->getList();

        $this->assertCount(1, $list);
        $this->assertInstanceOf(Chunk::class, $list[0]);
        $this->assertEquals("urn:mace:shibboleth:metadata:1.0", $list[0]->getNamespaceURI());
    }


    /**
     * Adding a non-namespaced element to an md:Extensions element should throw an exception
     */
    public function testMarshallingWithNonNamespacedExtensions(): void
    {
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage('Extensions MUST NOT include global (non-namespace-qualified) elements.');

        new Extensions([new Chunk(DOMDocumentFactory::fromString('<child/>')->documentElement)]);
    }


    /**
     * Adding an element from SAML-defined namespaces element should throw an exception
     */
    public function testMarshallingWithSamlDefinedNamespacedExtensions(): void
    {
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage('Extensions MUST NOT include any SAML-defined namespace elements.');

        new Extensions([new AttributeValue('something')]);
    }
}
