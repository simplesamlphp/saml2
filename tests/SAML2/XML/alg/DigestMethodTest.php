<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\alg;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\alg\DigestMethod;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\alg\DigestMethodTest
 *
 * @covers \SimpleSAML\SAML2\XML\alg\AbstractAlgElement
 * @covers \SimpleSAML\SAML2\XML\alg\DigestMethod
 *
 * @package simplesamlphp/saml2
 */
final class DigestMethodTest extends TestCase
{
    use SerializableElementTestTrait;
    use SchemaValidationTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5)
            . '/schemas/sstc-saml-metadata-algsupport-v1.0.xsd';

        $this->testedClass = DigestMethod::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/alg_DigestMethod.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $digestMethod = new DigestMethod(
            C::DIGEST_SHA256,
            [
                new Chunk(DOMDocumentFactory::fromString(
                    '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Some</ssp:Chunk>'
                )->documentElement)
            ],
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($digestMethod),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $digestMethod = DigestMethod::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($digestMethod),
        );
    }


    /**
     */
    public function testUnmarshallingMissingAlgorithmThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->removeAttribute('Algorithm');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'Algorithm' attribute on alg:DigestMethod.");

        DigestMethod::fromXML($document);
    }
}
