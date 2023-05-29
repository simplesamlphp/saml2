<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\OneTimeUse;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\OneTimeUseTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\OneTimeUse
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractConditionType
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class OneTimeUseTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        self::$testedClass = OneTimeUse::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_OneTimeUse.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $oneTimeUse = new OneTimeUse();

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($oneTimeUse),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $oneTimeUse = OneTimeUse::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($oneTimeUse),
        );
    }
}
