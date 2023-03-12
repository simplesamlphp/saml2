<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\GetComplete;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\GetCompleteTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\GetComplete
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class GetCompleteTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        $this->testedClass = GetComplete::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/samlp_GetComplete.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $getComplete = new GetComplete('https://some/location');

        $getCompleteElement = $getComplete->toXML();
        $this->assertEquals('https://some/location', $getCompleteElement->textContent);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($getComplete),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $getComplete = GetComplete::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($getComplete),
        );
    }
}
