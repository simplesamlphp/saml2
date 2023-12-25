<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\ecp;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\ecp\Request;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\GetComplete;
use SimpleSAML\SAML2\XML\samlp\IDPEntry;
use SimpleSAML\SAML2\XML\samlp\IDPList;
use SimpleSAML\SOAP\Constants as SOAP;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * @covers \SimpleSAML\SAML2\XML\ecp\AbstractEcpElement
 * @covers \SimpleSAML\SAML2\XML\ecp\Request
 * @package simplesamlphp/saml2
 */
final class RequestTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-ecp-2.0.xsd';

        self::$testedClass = Request::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/ecp_Request.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer(
            'TheIssuerValue',
            'TheNameQualifier',
            'TheSPNameQualifier',
            'urn:the:format',
            'TheSPProvidedID',
        );
        $entry1 = new IDPEntry('urn:some:requester1', 'testName1', 'urn:test:testLoc1');
        $entry2 = new IDPEntry('urn:some:requester2', 'testName2', 'urn:test:testLoc2');
        $getComplete = new GetComplete('https://some/location');
        $idpList = new IDPList([$entry1, $entry2], $getComplete);

        $request = new Request($issuer, $idpList, 'PHPUnit', true);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($request),
        );
    }


    /**
     */
    public function testUnmarshallingWithMissingMustUnderstandThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV_11, 'mustUnderstand');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:mustUnderstand attribute in <ecp:Request>.');

        Request::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV_11, 'actor');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:actor attribute in <ecp:Request>.');

        Request::fromXML($document);
    }
}
