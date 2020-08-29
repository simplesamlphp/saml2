<?php

declare(strict_types=1);

namespace \SimpleSAML\SAML2\XML\alg;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Exception\MissingAttributeException;
use SimpleSAML\SAML2\XML\alg\DigestMethod;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\alg\DigestMethodTest
 *
 * @covers \SimpleSAML\SAML2\XML\alg\DigestMethod
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
final class DigestMethodTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromString(
            '<alg:DigestMethod xmlns:alg="' . DigestMethod::NS . '" Algorithm="http://exampleAlgorithm" />'
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $digestMethod = new DigestMethod('http://exampleAlgorithm');

        $this->assertEquals('http://exampleAlgorithm', $digestMethod->getAlgorithm());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($digestMethod));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $digestMethod = DigestMethod::fromXML($this->document->documentElement);

        $this->assertEquals('http://exampleAlgorithm', $digestMethod->getAlgorithm());
    }


    /**
     * @return void
     */
    public function testUnmarshallingMissingAlgorithmThrowsException(): void
    {
        $document = $this->document->documentElement;
        $document->removeAttribute('Algorithm');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'Algorithm' attribute on alg:DigestMethod.");

        DigestMethod::fromXML($document);
    }

    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(DigestMethod::fromXML($this->document->documentElement))))
        );
    }
}
