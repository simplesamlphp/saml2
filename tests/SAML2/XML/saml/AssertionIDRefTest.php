<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\AssertionIDRef;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AssertionIDRefTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AssertionIDRef
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class AssertionIDRefTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = AssertionIDRef::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/saml_AssertionIDRef.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $assertionIDRef = new AssertionIDRef('_Test');

        $assertionIDRefElement = $assertionIDRef->toXML();
        $this->assertEquals('_Test', $assertionIDRefElement->textContent);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($assertionIDRef),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $assertionIDRef = AssertionIDRef::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($assertionIDRef),
        );
    }
}
