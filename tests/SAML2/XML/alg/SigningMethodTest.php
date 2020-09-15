<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\alg;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\SAML2\XML\alg\SigningMethod;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\alg\SigningMethodTest
 *
 * @covers \SimpleSAML\SAML2\XML\alg\AbstractAlgElement
 * @covers \SimpleSAML\SAML2\XML\alg\SigningMethod
 *
 * @package simplesamlphp/saml2
 */
final class SigningMethodTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/alg_SigningMethod.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $signingMethod = new SigningMethod('http://exampleAlgorithm', 1024, 4096);

        $this->assertEquals('http://exampleAlgorithm', $signingMethod->getAlgorithm());
        $this->assertEquals(1024, $signingMethod->getMinKeySize());
        $this->assertEquals(4096, $signingMethod->getMaxKeySize());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($signingMethod));
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $signingMethod = SigningMethod::fromXML($this->document->documentElement);

        $this->assertEquals('http://exampleAlgorithm', $signingMethod->getAlgorithm());
        $this->assertEquals(1024, $signingMethod->getMinKeySize());
        $this->assertEquals(4096, $signingMethod->getMaxKeySize());
    }


    /**
     */
    public function testMissingAlgorithmThrowsException(): void
    {
        $document = $this->document->documentElement;
        $document->removeAttribute('Algorithm');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'Algorithm' attribute on alg:SigningMethod.");

        SigningMethod::fromXML($document);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(SigningMethod::fromXML($this->document->documentElement))))
        );
    }
}
