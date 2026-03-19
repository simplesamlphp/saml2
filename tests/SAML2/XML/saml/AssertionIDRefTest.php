<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\AssertionIDRef;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AssertionIDRefTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(AssertionIDRef::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AssertionIDRefTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AssertionIDRef::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AssertionIDRef.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $assertionIDRef = AssertionIDRef::fromString('_Test');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($assertionIDRef),
        );
    }
}
