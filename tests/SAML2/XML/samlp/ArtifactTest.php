<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\{AbstractSamlpElement, Artifact};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\Base64BinaryValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\ArtifactTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(Artifact::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class ArtifactTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Artifact::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_Artifact.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $artifact = new Artifact(
            Base64BinaryValue::fromString('AAQAAM0ARI+cUaUKAx19/KC3fOV/vznNj8oE0JKKPQC8nTesXxPke7uRy+8='),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($artifact),
        );
    }
}
