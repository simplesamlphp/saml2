<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\AssertionURIRef;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AssertionURIRefTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AssertionURIRef
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class AssertionURIRefTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = AssertionURIRef::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/saml_AssertionURIRef.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $assertionURIRef = new AssertionURIRef('urn:x-simplesamlphp:reference');

        $assertionURIRefElement = $assertionURIRef->toXML();
        $this->assertEquals('urn:x-simplesamlphp:reference', $assertionURIRefElement->textContent);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($assertionURIRef),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $assertionURIRef = AssertionURIRef::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($assertionURIRef),
        );
    }
}
