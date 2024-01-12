<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\samlp\Extensions;
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
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = Extensions::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_Extensions.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $ext1 = DOMDocumentFactory::fromString(<<<XML
  <myns:AttributeList xmlns:myns="urn:test:mynamespace">
    <myns:Attribute name="UserName" value=""/>
  </myns:AttributeList>
XML
        );

        $ext2 = DOMDocumentFactory::fromString(
            '<myns:ExampleElement xmlns:myns="urn:test:mynamespace" name="AnotherExtension" />',
        );

        $extensions = new Extensions(
            [new Chunk($ext1->documentElement), new Chunk($ext2->documentElement)],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($extensions)
        );
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
