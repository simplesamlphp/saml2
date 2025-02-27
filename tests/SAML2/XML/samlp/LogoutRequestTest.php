<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\{EncryptedID, Issuer, NameID};
use SimpleSAML\SAML2\XML\samlp\{
    AbstractMessage,
    AbstractRequest,
    AbstractSamlpElement,
    LogoutRequest,
    SessionIndex,
};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\{MissingElementException, TooManyElementsException};
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\IDValue;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\{PEMCertificatesMock, SignedElementTestTrait};

use function dirname;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\LogoutRequestTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(LogoutRequest::class)]
#[CoversClass(AbstractRequest::class)]
#[CoversClass(AbstractMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class LogoutRequestTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;


    /**
     * Load a fixture.
     */
    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();

        self::$testedClass = LogoutRequest::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_LogoutRequest.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID(
            SAMLStringValue::fromString('NameIDValue'),
        );

        $logoutRequest = new LogoutRequest(
            id: IDValue::fromString('SomeIDValue'),
            identifier: $nameId,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            sessionIndexes: [
                new SessionIndex(SAMLStringValue::fromString('SessionIndexValue')),
            ],
        );

        $logoutRequestElement = $logoutRequest->toXML();
        $this->assertEquals('LogoutRequest', $logoutRequestElement->localName);
        $this->assertEquals(C::NS_SAMLP, $logoutRequestElement->namespaceURI);

        $xpCache = XPath::getXPath($logoutRequestElement);
        $nameIdElements = XPath::xpQuery($logoutRequestElement, './saml_assertion:NameID', $xpCache);
        $this->assertCount(1, $nameIdElements);
        $nameIdElements = $nameIdElements[0];
        $this->assertEquals('NameIDValue', $nameIdElements->textContent);

        $sessionIndexElements = XPath::xpQuery($logoutRequestElement, './saml_protocol:SessionIndex', $xpCache);
        $this->assertCount(1, $sessionIndexElements);
        $this->assertEquals('SessionIndexValue', $sessionIndexElements[0]->textContent);

        $nameId = new NameID(
            SAMLStringValue::fromString('NameIDValue'),
        );
        $logoutRequest = new LogoutRequest(
            id: IDValue::fromString('SomeIDValue'),
            identifier: $nameId,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            sessionIndexes: [
                new SessionIndex(SAMLStringValue::fromString('SessionIndexValue1')),
                new SessionIndex(SAMLStringValue::fromString('SessionIndexValue2')),
            ],
        );
        $logoutRequestElement = $logoutRequest->toXML();

        $xpCache = XPath::getXPath($logoutRequestElement);
        $sessionIndexElements = XPath::xpQuery($logoutRequestElement, './saml_protocol:SessionIndex', $xpCache);
        $this->assertCount(2, $sessionIndexElements);
        $this->assertEquals('SessionIndexValue1', $sessionIndexElements[0]->textContent);
        $this->assertEquals('SessionIndexValue2', $sessionIndexElements[1]->textContent);
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $nameId = new NameID(
            SAMLStringValue::fromString('NameIDValue'),
        );

        $logoutRequest = new LogoutRequest(
            id: IDValue::fromString('SomeIDValue'),
            identifier: $nameId,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            sessionIndexes: [
                new SessionIndex(SAMLStringValue::fromString('SessionIndexValue')),
            ],
        );

        $logoutRequestElement = $logoutRequest->toXML();

        // Test for a NameID
        $xpCache = XPath::getXPath($logoutRequestElement);
        $logoutRequestElements = XPath::xpQuery($logoutRequestElement, './saml_assertion:NameID', $xpCache);
        $this->assertCount(1, $logoutRequestElements);

        // Test ordering of LogoutRequest contents
        /** @psalm-var \DOMElement[] $logoutRequestElements */
        $logoutRequestElements = XPath::xpQuery(
            $logoutRequestElement,
            './saml_assertion:NameID/following-sibling::*',
            $xpCache,
        );

        $this->assertCount(1, $logoutRequestElements);
        $this->assertEquals('samlp:SessionIndex', $logoutRequestElements[0]->tagName);
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $container = ContainerSingleton::getInstance();
        $container->setBlacklistedAlgorithms(null);

        $logoutRequest = LogoutRequest::fromXML(self::$xmlRepresentation->documentElement);
        $issuer = $logoutRequest->getIssuer();

        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals('urn:x-simplesamlphp:issuer', $issuer->getContent());

        $encid = $logoutRequest->getIdentifier();
        $this->assertInstanceOf(EncryptedID::class, $encid);

        $sessionIndexes = $logoutRequest->getSessionIndexes();
        $this->assertCount(2, $sessionIndexes);
        $this->assertEquals('SomeSessionIndex1', $sessionIndexes[0]->getContent()->getValue());
        $this->assertEquals('SomeSessionIndex2', $sessionIndexes[1]->getContent()->getValue());

        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm()->getValue(),
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
        );
        $identifier = $encid->decrypt($decryptor);
        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals('very secret', $identifier->getContent());
    }


    /**
     */
    public function testEncryptedNameId(): void
    {
        $eid = EncryptedID::fromXML(DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_EncryptedID.xml',
        )->documentElement);

        $logoutRequest = new LogoutRequest(
            identifier: $eid,
            id: IDValue::fromString('SomeIDValue'),
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );
        $logoutRequestElement = $logoutRequest->toXML();
        $this->assertCount(
            1,
            XPath::xpQuery(
                $logoutRequestElement,
                './saml_assertion:EncryptedID/xenc:EncryptedData',
                XPath::getXPath($logoutRequestElement),
            ),
        );
    }


    /**
     */
    public function testPlainNameIDUnmarshalling(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="SomeIDValue"
    Version="2.0"
    IssueInstant="2010-07-22T11:30:19Z">
  <saml:Issuer>urn:x-simplesamlphp:issuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($logoutRequestElement);
        $identifier = $logoutRequest->getIdentifier();

        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals("frits", $identifier->getContent());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified', $identifier->getFormat());
    }


    /**
     */
    public function testMissingNameIDThrowsException(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="SomeIDValue"
    IssueInstant="2010-07-22T11:30:19Z">
  <saml:Issuer>urn:x-simplesamlphp</saml:Issuer>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $logoutRequestElement = $document->documentElement;

        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage(
            "Missing <saml:NameID>, <saml:BaseID> or <saml:EncryptedID> in <samlp:LogoutRequest>.",
        );
        LogoutRequest::fromXML($logoutRequestElement);
    }


    /**
     */
    public function testMultipleNameIDThrowsException(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="SomeIDValue"
    IssueInstant="2010-07-22T11:30:19Z">
  <saml:Issuer>urn:x-simplesamlphp:issuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">willem</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $logoutRequestElement = $document->documentElement;

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:NameID> in <samlp:LogoutRequest>.");
        LogoutRequest::fromXML($logoutRequestElement);
    }


    /**
     */
    public function testGetNotOnOrAfter(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="SomeIDValue"
    IssueInstant="2010-07-22T11:30:19Z"
    NotOnOrAfter="2018-11-28T19:33:12Z">
  <saml:Issuer>urn:x-simplesamlphp:issuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($logoutRequestElement);
        $this->assertEquals('2018-11-28T19:33:12Z', $logoutRequest->getNotOnOrAfter()->getValue());
    }


    /**
     */
    public function testSetNotOnOrAfter(): void
    {
        $nameId = new NameID(
            SAMLStringValue::fromString('NameIDValue'),
        );

        $logoutRequest = new LogoutRequest(
            IDValue::fromString('SomeIDValue'),
            $nameId,
            SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );
        $logoutRequestElement = $logoutRequest->toXML();

        $logoutRequest2 = LogoutRequest::fromXML($logoutRequestElement);
        $this->assertEquals(
            SAMLDateTimeValue::fromDateTime(self::$clock->now())->getValue(),
            $logoutRequest2->getNotOnOrAfter()->getValue(),
        );
    }


    /**
     */
    public function testGetReason(): void
    {
        $reason = "urn:simplesamlphp:reason-test";
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="SomeIDValue"
    IssueInstant="2010-07-22T11:30:19Z"
    NotOnOrAfter="2018-11-28T19:33:12Z"
    Reason="$reason">
  <saml:Issuer>urn:x-simplesamlphp:issuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($logoutRequestElement);
        $this->assertEquals($reason, $logoutRequest->getReason());
    }


    /**
     */
    public function testSetReason(): void
    {
        $reason = SAMLStringValue::fromString("urn:simplesamlphp:reason-test");
        $nameId = new NameID(
            SAMLStringValue::fromString('NameIDValue'),
        );

        $logoutRequest = new LogoutRequest(
            id: IDValue::fromString('SomeIDValue'),
            identifier: $nameId,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            notOnOrAfter: null,
            reason: $reason,
        );
        $logoutRequestElement = $logoutRequest->toXML();

        $logoutRequest2 = LogoutRequest::fromXML($logoutRequestElement);
        $this->assertEquals($reason, $logoutRequest2->getReason());
    }


    /**
     */
    public function testWithOutSessionIndices(): void
    {
        $xml = <<<XML
<samlp:LogoutRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    Version="2.0"
    ID="SomeIDValue"
    IssueInstant="2010-07-22T11:30:19Z">
  <saml:Issuer>urn:x-simplesamlphp:issuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($logoutRequestElement);
        $this->assertCount(0, $logoutRequest->getSessionIndexes());
    }


    /**
     */
    public function testSetSessionIndicesVariants(): void
    {
        $nameId = new NameID(
            SAMLStringValue::fromString('test'),
        );

        $sessionIndexes = [
            new SessionIndex(SAMLStringValue::fromString('SessionIndexValue1')),
            new SessionIndex(SAMLStringValue::fromString('SessionIndexValue2')),
        ];

        $logoutRequest = new LogoutRequest(
            id: IDValue::fromString('SomeIDValue'),
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            identifier: $nameId,
            sessionIndexes: $sessionIndexes,
        );

        $sessionIndexes = $logoutRequest->getSessionIndexes();
        $this->assertCount(2, $sessionIndexes);
        $this->assertEquals('SessionIndexValue1', $sessionIndexes[0]->getContent()->getValue());
        $this->assertEquals('SessionIndexValue2', $sessionIndexes[1]->getContent()->getValue());
    }
}
