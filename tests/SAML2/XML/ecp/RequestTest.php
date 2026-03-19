<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\ecp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\ecp\AbstractEcpElement;
use SimpleSAML\SAML2\XML\ecp\Request;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\GetComplete;
use SimpleSAML\SAML2\XML\samlp\IDPEntry;
use SimpleSAML\SAML2\XML\samlp\IDPList;
use SimpleSAML\SOAP11\Constants as SOAP;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;
use SimpleSAML\XMLSchema\Type\BooleanValue;

use function dirname;
use function strval;

/**
 * @package simplesamlphp/saml2
 */
#[Group('ecp')]
#[CoversClass(Request::class)]
#[CoversClass(AbstractEcpElement::class)]
final class RequestTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Request::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/ecp_Request.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer(
            SAMLStringValue::fromString('urn:x-simplesamlphp:issuer'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
            SAMLAnyURIValue::fromString('urn:the:format'),
            SAMLStringValue::fromString('TheSPProvidedID'),
        );
        $entry1 = new IDPEntry(
            EntityIDValue::fromString('urn:some:requester1'),
            SAMLStringValue::fromString('testName1'),
            SAMLAnyURIValue::fromString('urn:test:testLoc1'),
        );
        $entry2 = new IDPEntry(
            EntityIDValue::fromString('urn:some:requester2'),
            SAMLStringValue::fromString('testName2'),
            SAMLAnyURIValue::fromString('urn:test:testLoc2'),
        );
        $getComplete = GetComplete::fromString('https://some/location');
        $idpList = new IDPList([$entry1, $entry2], $getComplete);

        $request = new Request(
            $issuer,
            $idpList,
            SAMLStringValue::fromString('PHPUnit'),
            BooleanValue::fromBoolean(true),
        );

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
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV, 'mustUnderstand');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:mustUnderstand attribute in <ecp:Request>.');

        Request::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV, 'actor');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:actor attribute in <ecp:Request>.');

        Request::fromXML($document);
    }
}
