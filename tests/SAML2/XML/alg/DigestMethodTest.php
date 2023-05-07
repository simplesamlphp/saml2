<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\alg;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\alg\DigestMethod;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\alg\DigestMethodTest
 *
 * @author Jaime Pérez Crespo, UNINETT AS <jaime.perez@uninett.no>
 * @package simplesamlphp/saml2
 */
class DigestMethodTest extends TestCase
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

        $xpCache = XPath::getXPath($xml);
        $digestMethodElements = XPath::xpQuery(
            $xml,
            '/root/*[local-name()=\'DigestMethod\'' .
            ' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:algsupport\']',
            $xpCache,
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
        $this->expectException(Exception::class, 'Missing required attribute "Algorithm"');
        new DigestMethod($document->firstChild);
    }
}
