<?php

declare(strict_types=1);

namespace SAML2\XML\alg;

use Exception;
use SAML2\DOMDocumentFactory;
use SAML2\XML\alg\SigningMethod;
use SAML2\Utils;

/**
 * Class \SAML2\XML\alg\SigningMethodTest
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
class SigningMethodTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $signingMethod = new SigningMethod('http://exampleAlgorithm', 1024, 2048);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $signingMethod->toXML($document->firstChild);

        $signingMethodElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'SigningMethod\' and namespace-uri()=\'' . SigningMethod::NS . '\']'
        );
        $this->assertCount(1, $signingMethodElements);
        $signingMethodElement = $signingMethodElements[0];

        $this->assertEquals('http://exampleAlgorithm', $signingMethodElement->getAttribute('Algorithm'));
        $this->assertEquals('1024', $signingMethodElement->getAttribute('MinKeySize'));
        $this->assertEquals('2048', $signingMethodElement->getAttribute('MaxKeySize'));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<alg:SigningMethod xmlns:alg="' . SigningMethod::NS . '" Algorithm="http://exampleAlgorithm"'
                . ' MinKeySize="1024" MaxKeySize="4096" />'
        );

        $signingMethod = SigningMethod::fromXML($document->firstChild);

        $this->assertEquals('http://exampleAlgorithm', $signingMethod->getAlgorithm());
        $this->assertEquals(1024, $signingMethod->getMinKeySize());
        $this->assertEquals(4096, $signingMethod->getMaxKeySize());
    }


    /**
     * @return void
     */
    public function testMissingAlgorithmThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<alg:SigningMethod xmlns:alg="' . SigningMethod::NS . '" MinKeySize="1024" MaxKeySize="4096" />'
        );
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing required attribute "Algorithm"');
        SigningMethod::fromXML($document->firstChild);
    }
}
