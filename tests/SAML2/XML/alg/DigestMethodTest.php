<?php

namespace SAML2\XML\alg;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\alg\DigestMethodTest
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
class DigestMethodTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $digestMethod = new DigestMethod();
        $digestMethod->Algorithm = 'http://exampleAlgorithm';

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $digestMethod->toXML($document->firstChild);

        $digestMethodElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'DigestMethod\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:algsupport\']'
        );
        $this->assertCount(1, $digestMethodElements);
        $digestMethodElement = $digestMethodElements[0];
        $this->assertEquals('http://exampleAlgorithm', $digestMethodElement->getAttribute('Algorithm'));
    }


    public function testUnmarshalling()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<alg:DigestMethod xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport"
                  Algorithm="http://exampleAlgorithm" />
XML
        );

        $digestMethod = new DigestMethod($document->firstChild);
        $this->assertEquals('http://exampleAlgorithm', $digestMethod->Algorithm);
    }


    public function testMissingAlgorithmThrowsException()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<alg:DigestMethod xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport" />
XML
        );
        $this->setExpectedException('Exception', 'Missing required attribute "Algorithm"');
        new DigestMethod($document->firstChild);
    }
}
