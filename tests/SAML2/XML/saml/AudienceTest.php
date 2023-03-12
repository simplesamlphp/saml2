<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AudienceTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Audience
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractConditionType
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class AudienceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = Audience::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/saml_Audience.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $audience = new Audience('urn:test:audience1');

        $audienceElement = $audience->toXML();
        $this->assertEquals('urn:test:audience1', $audienceElement->textContent);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($audience),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $audience = Audience::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($audience),
        );
    }
}
