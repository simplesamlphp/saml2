<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\samlp\{AbstractSamlpElement, StatusMessage};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\StatusMessageTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(StatusMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class StatusMessageTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = StatusMessage::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_StatusMessage.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $statusMessage = new StatusMessage(
            SAMLStringValue::fromString('Something went wrong'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($statusMessage),
        );
    }
}
