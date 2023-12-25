<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\GetComplete;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
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
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = GetComplete::class;

        self::$arrayRepresentation = ['https://some/location'];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_GetComplete.xml',
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
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($getComplete),
        );
    }
}
