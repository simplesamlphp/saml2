<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\alg;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\alg\AbstractAlgElement;
use SimpleSAML\SAML2\XML\alg\DigestMethod;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\alg\DigestMethodTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('alg')]
#[CoversClass(DigestMethod::class)]
#[CoversClass(AbstractAlgElement::class)]
final class DigestMethodTest extends TestCase
{
    use SerializableElementTestTrait;
    use SchemaValidationTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = DigestMethod::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/alg_DigestMethod.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $digestMethod = new DigestMethod(
            SAMLAnyURIValue::fromString(C::DIGEST_SHA256),
            [
                new Chunk(DOMDocumentFactory::fromString(
                    '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Some</ssp:Chunk>',
                )->documentElement),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($digestMethod),
        );
    }


    /**
     */
    public function testUnmarshallingMissingAlgorithmThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttribute('Algorithm');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'Algorithm' attribute on alg:DigestMethod.");

        DigestMethod::fromXML($document);
    }
}
