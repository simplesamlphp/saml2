<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\SAML2\XML\samlp\{AbstractSamlpElement, RequesterID};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{ArrayizableElementTestTrait, SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\RequesterIDTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(RequesterID::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class RequesterIDTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = RequesterID::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_RequesterID.xml',
        );

        self::$arrayRepresentation = ['urn:some:requester'];
    }


    /**
     */
    public function testMarshalling(): void
    {
        $requesterId = new RequesterID(
            EntityIDValue::fromString('urn:some:requester'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($requesterId),
        );
    }
}
