<?php

declare(strict_types=1);

namespace SAML2\XML\alg;

use SAML2\DOMDocumentFactory;
use SAML2\XML\alg\DigestMethod;
use SAML2\Utils;

/**
 * Class \SAML2\XML\alg\DigestMethodTest
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
class DigestMethodTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $digestMethod = new DigestMethod('http://exampleAlgorithm');

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $digestMethod->toXML($document->firstChild);

        $digestMethodElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'DigestMethod\' and namespace-uri()=\'' . DigestMethod::NS . '\']'
        );
        $this->assertCount(1, $digestMethodElements);
        $digestMethodElement = $digestMethodElements[0];
        $this->assertEquals('http://exampleAlgorithm', $digestMethodElement->getAttribute('Algorithm'));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<alg:DigestMethod xmlns:alg="' . DigestMethod::NS . '" Algorithm="http://exampleAlgorithm" />'
        );

        $digestMethod = DigestMethod::fromXML($document->firstChild);
        $this->assertEquals('http://exampleAlgorithm', $digestMethod->getAlgorithm());
    }


    /**
     * @return void
     */
    public function testMissingAlgorithmThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<alg:DigestMethod xmlns:alg="' . DigestMethod::NS . '" />'
        );
        $this->expectException(\Exception::class, 'Missing required attribute "Algorithm"');
        DigestMethod::fromXML($document->firstChild);
    }
}
