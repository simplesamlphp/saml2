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
        $signingMethod = new SigningMethod();
        $signingMethod->setAlgorithm('http://exampleAlgorithm');

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $signingMethod->toXML($document->firstChild);

        /** @var \DOMElement[] $signingMethodElements */
        $signingMethodElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'SigningMethod\' and ' .
            'namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:algsupport\']'
        );
        $this->assertCount(1, $signingMethodElements);
        $signingMethodElement = $signingMethodElements[0];
        $this->assertEquals('http://exampleAlgorithm', $signingMethodElement->getAttribute('Algorithm'));
        $this->assertFalse($signingMethodElement->hasAttribute('MinKeySize'));
        $this->assertFalse($signingMethodElement->hasAttribute('MaxKeySize'));

        $signingMethod->setMinKeySize(1024);
        $signingMethod->setMaxKeySize(4096);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $signingMethod->toXML($document->firstChild);

        /** @var \DOMElement[] $signingMethodElements */
        $signingMethodElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'SigningMethod\' and ' .
            'namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:algsupport\']'
        );
        $this->assertCount(1, $signingMethodElements);
        $signingMethodElement = $signingMethodElements[0];
        $this->assertEquals(1024, $signingMethodElement->getAttribute('MinKeySize'));
        $this->assertEquals(4096, $signingMethodElement->getAttribute('MaxKeySize'));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<alg:SigningMethod xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport"
                   Algorithm="http://exampleAlgorithm"
                   MinKeySize="1024"
                   MaxKeySize="4096" />
XML
        );

        $signingMethod = new SigningMethod($document->firstChild);
        $this->assertEquals('http://exampleAlgorithm', $signingMethod->getAlgorithm());
        $this->assertEquals(1024, $signingMethod->getMinKeySize());
        $this->assertEquals(4096, $signingMethod->getMaxKeySize());
    }


    /**
     * @return void
     */
    public function testMissingAlgorithmThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<alg:SigningMethod xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport" 
                   MinKeySize="1024"
                   MaxKeySize="4096" />
XML
        );
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing required attribute "Algorithm"');
        new SigningMethod($document->firstChild);
    }
}
