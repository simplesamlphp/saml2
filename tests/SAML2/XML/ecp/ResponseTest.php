<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\ecp;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\ecp\AbstractEcpElement;
use SimpleSAML\SAML2\XML\ecp\Response;
use SimpleSAML\SOAP11\Constants as SOAP;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;

use function dirname;
use function strval;

/**
 * @package simplesamlphp/saml2
 */
#[Group('ecp')]
#[CoversClass(Response::class)]
#[CoversClass(AbstractEcpElement::class)]
final class ResponseTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Response::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/ecp_Response.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $response = new Response(
            SAMLAnyURIValue::fromString('https://example.com/ACS'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($response),
        );
    }


    /**
     */
    public function testToXMLResponseAppended(): void
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $element = $doc->createElement('Foobar');

        $response = new Response(
            SAMLAnyURIValue::fromString('https://example.com/ACS'),
        );
        $return = $response->toXML($element);

        $elements = $element->getElementsByTagNameNS(C::NS_ECP, 'Response');

        $this->assertEquals(1, $elements->length);
        $this->assertEquals($return, $elements->item(0));
    }


    /**
     */
    public function testUnmarshallingWithMissingMustUnderstandThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV, 'mustUnderstand');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:mustUnderstand attribute in <ecp:Response>.');

        Response::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV, 'actor');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:actor attribute in <ecp:Response>.');

        Response::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingACSThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttribute('AssertionConsumerServiceURL');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing AssertionConsumerServiceURL attribute in <ecp:Response>.');

        Response::fromXML($document);
    }
}
