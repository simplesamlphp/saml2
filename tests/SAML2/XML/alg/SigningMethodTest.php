<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\alg;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\alg\{AbstractAlgElement, SigningMethod};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\{Chunk, DOMDocumentFactory};
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\PositiveIntegerValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\alg\SigningMethodTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('alg')]
#[CoversClass(SigningMethod::class)]
#[CoversClass(AbstractAlgElement::class)]
final class SigningMethodTest extends TestCase
{
    use SerializableElementTestTrait;
    use SchemaValidationTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = SigningMethod::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/alg_SigningMethod.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $signingMethod = new SigningMethod(
            SAMLAnyURIValue::fromString(C::SIG_RSA_SHA256),
            PositiveIntegerValue::fromString('1024'),
            PositiveIntegerValue::fromString('4096'),
            [
                new Chunk(DOMDocumentFactory::fromString(
                    '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Some</ssp:Chunk>',
                )->documentElement),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($signingMethod),
        );
    }


    /**
     */
    public function testMissingAlgorithmThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttribute('Algorithm');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'Algorithm' attribute on alg:SigningMethod.");

        SigningMethod::fromXML($document);
    }
}
