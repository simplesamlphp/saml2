<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\saml\{AbstractSamlElement, Action};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\ActionTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(Action::class)]
#[CoversClass(AbstractSamlElement::class)]
final class ActionTest extends TestCase
{
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Action::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Action.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $action = new Action(
            SAMLAnyURIValue::fromString(C::NAMESPACE),
            SAMLStringValue::fromString('SomeAction'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($action),
        );
    }
}
