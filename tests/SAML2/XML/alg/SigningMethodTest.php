<?php

namespace SAML2\XML\alg;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\alg\SigningMethodTest
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
class SigningMethodTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $signingMethod = new SigningMethod();
        $signingMethod->Algorithm = 'http://exampleAlgorithm';

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $signingMethod->toXML($document->firstChild);

        $signingMethodElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'SigningMethod\' and '.
            'namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:algsupport\']'
        );
        $this->assertCount(1, $signingMethodElements);
        $signingMethodElement = $signingMethodElements[0];
        $this->assertEquals('http://exampleAlgorithm', $signingMethodElement->getAttribute('Algorithm'));
        $this->assertFalse($signingMethodElement->hasAttribute('MinKeySize'));
        $this->assertFalse($signingMethodElement->hasAttribute('MaxKeySize'));

        $signingMethod->MinKeySize = 1024;
        $signingMethod->MaxKeySize = 4096;

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $signingMethod->toXML($document->firstChild);

        $signingMethodElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'SigningMethod\' and '.
            'namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:algsupport\']'
        );
        $this->assertCount(1, $signingMethodElements);
        $signingMethodElement = $signingMethodElements[0];
        $this->assertEquals(1024, $signingMethodElement->getAttribute('MinKeySize'));
        $this->assertEquals(4096, $signingMethodElement->getAttribute('MaxKeySize'));
    }


    public function testUnmarshalling()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<alg:SigningMethod xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport"
                   Algorithm="http://exampleAlgorithm"
                   MinKeySize="1024"
                   MaxKeySize="4096" />
XML
        );

        $signingMethod = new SigningMethod($document->firstChild);
        $this->assertEquals('http://exampleAlgorithm', $signingMethod->Algorithm);
        $this->assertEquals(1024, $signingMethod->MinKeySize);
        $this->assertEquals(4096, $signingMethod->MaxKeySize);
    }


    public function testMissingAlgorithmThrowsException()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<alg:SigningMethod xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport" 
                   MinKeySize="1024"
                   MaxKeySize="4096" />
XML
        );
        $this->setExpectedException('Exception', 'Missing required attribute "Algorithm"');
        new SigningMethod($document->firstChild);
    }
}
