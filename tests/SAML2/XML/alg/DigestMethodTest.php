<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\alg;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\SAML2\XML\alg\DigestMethod;
use SimpleSAML\SAML2\Utils;

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
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = DigestMethod::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/alg_DigestMethod.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $digestMethod = new DigestMethod('http://exampleAlgorithm');

        $this->assertEquals('http://exampleAlgorithm', $digestMethod->getAlgorithm());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($digestMethod)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $digestMethod = DigestMethod::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('http://exampleAlgorithm', $digestMethod->getAlgorithm());
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
