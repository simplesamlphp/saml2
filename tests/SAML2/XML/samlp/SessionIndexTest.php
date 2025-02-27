<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\samlp\{AbstractSamlpElement, SessionIndex};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\SessionIndexTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(SessionIndex::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class SessionIndexTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = SessionIndex::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_SessionIndex.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $sessionIndex = new SessionIndex(
            SAMLStringValue::fromString('SomeSessionIndex1'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($sessionIndex),
        );
    }
}
