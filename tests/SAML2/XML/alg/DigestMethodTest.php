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
        $digestMethod = new DigestMethod();
        $digestMethod->setAlgorithm('http://exampleAlgorithm');

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


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<alg:DigestMethod xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport"
                  Algorithm="http://exampleAlgorithm" />
XML
        );

        $digestMethod = new DigestMethod($document->firstChild);
        $this->assertEquals('http://exampleAlgorithm', $digestMethod->getAlgorithm());
    }


    /**
     * @return void
     */
    public function testMissingAlgorithmThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<alg:DigestMethod xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport" />
XML
        );
        $this->expectException(\Exception::class, 'Missing required attribute "Algorithm"');
        new DigestMethod($document->firstChild);
    }
}
