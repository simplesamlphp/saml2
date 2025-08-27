<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\SAML2\XML\md\{AbstractMdElement, AffiliateMember};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Tests for AffiliateMember.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(AffiliateMember::class)]
#[CoversClass(AbstractMdElement::class)]
final class AffiliateMemberTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AffiliateMember::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AffiliateMember.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a AffiliateMember object from scratch.
     */
    public function testMarshalling(): void
    {
        $affiliateMember = new AffiliateMember(
            EntityIDValue::fromString('https://some.entity.org/id'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($affiliateMember),
        );
    }
}
