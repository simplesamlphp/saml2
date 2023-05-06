<?php

declare(strict_types=1);

namespace SAML2\XML\ecp;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;
use SAML2\Constants as C;
use SAML2\Exception\ProtocolViolationException;
use SAML2\XML\ecp\Response;
use SimpleSAML\SOAP\Constants as SOAP;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * @covers SAML2\XML\ecp\AbstractEcpElement
 * @covers SAML2\XML\ecp\Response
 * @package simplesamlphp/saml2
 */
final class ResponseTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-ecp-2.0.xsd';

        $this->testedClass = Response::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/ecp_Response.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $response = new Response('https://example.com/ACS');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($response),
        );
    }


    /**
     */
    public function testToXMLResponseAppended(): void
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $element = $doc->createElement('Foobar');

        $response = new Response('https://example.com/ACS');
        $return = $response->toXML($element);

        $elements = $element->getElementsByTagNameNS(C::NS_ECP, 'Response');

        $this->assertEquals(1, $elements->length);
        $this->assertEquals($return, $elements->item(0));
    }


    /**
     */
    public function testInvalidACSThrowsException(): void
    {
        $this->expectException(SchemaViolationException::class);

        new Response('some non-url');
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $response = Response::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($response),
        );
    }


    /**
     */
    public function testUnmarshallingWithMissingMustUnderstandThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV_11, 'mustUnderstand');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:mustUnderstand attribute in <ecp:Response>.');

        Response::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV_11, 'actor');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:actor attribute in <ecp:Response>.');

        Response::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingACSThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->removeAttribute('AssertionConsumerServiceURL');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing AssertionConsumerServiceURL attribute in <ecp:Response>.');

        Response::fromXML($document);
    }
}
