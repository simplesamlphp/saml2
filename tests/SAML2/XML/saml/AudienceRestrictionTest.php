<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\AudienceRestrictionTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AudienceRestriction
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class AudienceRestrictionTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public function setup(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = AudienceRestriction::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AudienceRestriction.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $condition = new AudienceRestriction(
            [
                new Audience('urn:test:audience1'),
                new Audience('urn:test:audience2')
            ]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($condition)
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $condition = AudienceRestriction::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($condition)
        );
    }
}
