<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\alg;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\alg\SigningMethod;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XMLSecurity\Constants;

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
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = SigningMethod::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/alg_SigningMethod.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $signingMethod = new SigningMethod(Constants::SIG_RSA_SHA256, 1024, 4096);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($signingMethod)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $signingMethod = SigningMethod::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(Constants::SIG_RSA_SHA256, $signingMethod->getAlgorithm());
        $this->assertEquals(1024, $signingMethod->getMinKeySize());
        $this->assertEquals(4096, $signingMethod->getMaxKeySize());
    }


    /**
     */
    public function testMissingAlgorithmThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->removeAttribute('Algorithm');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'Algorithm' attribute on alg:SigningMethod.");

        SigningMethod::fromXML($document);
    }
}
