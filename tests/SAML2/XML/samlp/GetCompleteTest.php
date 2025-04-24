<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
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
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(GetComplete::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class GetCompleteTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public static function setUpBeforeClass(): void
    {
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
