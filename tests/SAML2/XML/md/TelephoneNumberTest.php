<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\TelephoneNumber;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

/**
 * Tests for SurName.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(TelephoneNumber::class)]
#[CoversClass(AbstractMdElement::class)]
final class TelephoneNumberTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = TelephoneNumber::class;

        self::$arrayRepresentation = ['+1234567890'];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_TelephoneNumber.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a TelehponeNumber object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new TelephoneNumber('+1234567890');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }
}
