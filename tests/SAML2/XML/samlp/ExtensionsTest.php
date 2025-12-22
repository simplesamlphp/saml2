<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\Extensions;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSchema\Type\StringValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\ExtensionsTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(Extensions::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class ExtensionsTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     * Prepare a basic DOMElement to test against
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Extensions::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_Extensions.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $ext1 = DOMDocumentFactory::fromString(
            <<<XML
  <myns:AttributeList xmlns:myns="urn:test:mynamespace">
    <myns:Attribute name="UserName" value=""/>
  </myns:AttributeList>
XML
            ,
        );

        $ext2 = DOMDocumentFactory::fromString(
            '<myns:ExampleElement xmlns:myns="urn:test:mynamespace" name="AnotherExtension" />',
        );

        $extensions = new Extensions(
            [new Chunk($ext1->documentElement), new Chunk($ext2->documentElement)],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($extensions),
        );
    }


    /**
     * Adding a non-namespaced element to an md:Extensions element should throw an exception
     */
    public function testMarshallingWithNonNamespacedExtensions(): void
    {
        $this->expectException(SchemaViolationException::class);

        new Extensions([new Chunk(DOMDocumentFactory::fromString('<child/>')->documentElement)]);
    }


    /**
     * Adding an element from SAML-defined namespaces element should throw an exception
     */
    public function testMarshallingWithSamlDefinedNamespacedExtensions(): void
    {
        $this->expectException(InvalidDOMElementException::class);

        new Extensions([new AttributeValue(StringValue::fromString('something'))]);
    }
}
