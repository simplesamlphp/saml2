<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\aslo;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\aslo\AbstractAsloElement;
use SimpleSAML\SAML2\XML\aslo\Asynchronous;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\aslo\AsynchronousTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('aslo')]
#[CoversClass(Asynchronous::class)]
#[CoversClass(AbstractAsloElement::class)]
final class AsynchronousTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Asynchronous::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/aslo_Asynchronous.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $asynchronous = new Asynchronous();

        $expectedXml = self::$xmlRepresentation->saveXml(self::$xmlRepresentation->documentElement);
        $this->assertNotFalse($expectedXml);
        $actualXml = strval($asynchronous);

        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);
    }
}
