<?php

declare(strict_types=1);

namespace SAML2\XML\alg;

use Exception;
use SAML2\DOMDocumentFactory;
use SAML2\XML\alg\DigestMethod;
use SAML2\Utils;

/**
 * Class \SAML2\XML\alg\DigestMethodTest
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
final class DigestMethodTest extends \PHPUnit\Framework\TestCase
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing required attribute "Algorithm"');

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
