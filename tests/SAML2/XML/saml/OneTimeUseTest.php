<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\{AbstractConditionType, AbstractSamlElement, OneTimeUse};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\OneTimeUseTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(OneTimeUse::class)]
#[CoversClass(AbstractConditionType::class)]
#[CoversClass(AbstractSamlElement::class)]
final class OneTimeUseTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
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
}
