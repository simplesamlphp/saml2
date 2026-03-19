<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\Terminate;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\TerminateTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(Terminate::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class TerminateTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Terminate::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_Terminate.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $terminate = new Terminate();

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($terminate),
        );
    }
}
