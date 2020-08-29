<?php

declare(strict_types=1);

namespace SAML2\XML\alg;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Exception\MissingAttributeException;
use SimpleSAML\SAML2\XML\alg\SigningMethod;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\alg\SigningMethodTest
 *
 * @covers \SAML2\XML\alg\SigningMethod
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
final class SigningMethodTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    protected function setUp(): void
    {
        $ns = SigningMethod::NS;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<alg:SigningMethod xmlns:alg="{$ns}" Algorithm="http://exampleAlgorithm" MinKeySize="1024" MaxKeySize="4096" />
XML
        );
    }


    /**
     * @return void
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
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $signingMethod = SigningMethod::fromXML($this->document->documentElement);

        $this->assertEquals('http://exampleAlgorithm', $signingMethod->getAlgorithm());
        $this->assertEquals(1024, $signingMethod->getMinKeySize());
        $this->assertEquals(4096, $signingMethod->getMaxKeySize());
    }


    /**
     * @return void
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
