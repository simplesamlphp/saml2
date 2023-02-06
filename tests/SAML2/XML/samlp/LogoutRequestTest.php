<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use DOMElement;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\samlp\LogoutRequest;
use SimpleSAML\SAML2\XML\samlp\SessionIndex;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\Alg\Encryption\EncryptionAlgorithmFactory;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\xenc\CipherData;
use SimpleSAML\XMLSecurity\XML\xenc\CipherValue;
use SimpleSAML\XMLSecurity\XML\xenc\DataReference;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedData;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptionMethod;
use SimpleSAML\XMLSecurity\XML\xenc\ReferenceList;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\samlp\LogoutRequestTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\LogoutRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class LogoutRequestTest extends MockeryTestCase
{
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /** @var \DOMElement */
    private DOMElement $logoutRequestElement;


    /**
     * Load a fixture.
     */
    public function setUp(): void
    {
        $this->testedClass = LogoutRequest::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_LogoutRequest.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID('NameIDValue');

        $logoutRequest = new LogoutRequest(
            identifier: $nameId,
            sessionIndexes: [new SessionIndex('SessionIndexValue')],
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

        $nameId = new NameID('NameIDValue');
        $logoutRequest = new LogoutRequest(
            identifier: $nameId,
            sessionIndexes: [new SessionIndex('SessionIndexValue1'), new SessionIndex('SessionIndexValue2')],
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
        $nameId = new NameID('NameIDValue');

        $logoutRequest = new LogoutRequest(
            identifier: $nameId,
            sessionIndexes: [new SessionIndex('SessionIndexValue')],
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

        $logoutRequest = LogoutRequest::fromXML($this->xmlRepresentation->documentElement);
        $issuer = $logoutRequest->getIssuer();

        $this->assertInstanceOf(Issuer::class, $issuer);
        $this->assertEquals('urn:test:TheIssuer', $issuer->getContent());

        $encid = $logoutRequest->getIdentifier();
        $this->assertInstanceOf(EncryptedID::class, $encid);

        $sessionIndexes = $logoutRequest->getSessionIndexes();
        $this->assertCount(2, $sessionIndexes);
        $this->assertEquals('SomeSessionIndex1', $sessionIndexes[0]->getContent());
        $this->assertEquals('SomeSessionIndex2', $sessionIndexes[1]->getContent());

        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm(),
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

        $logoutRequest = new LogoutRequest($eid);
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
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($this->logoutRequestElement);
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
  <saml:Issuer>TheIssuer</saml:Issuer>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage(
            "Missing <saml:NameID>, <saml:BaseID> or <saml:EncryptedID> in <samlp:LogoutRequest>.",
        );
        LogoutRequest::fromXML($this->logoutRequestElement);
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
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">willem</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:NameID> in <samlp:LogoutRequest>.");
        LogoutRequest::fromXML($this->logoutRequestElement);
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
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($this->logoutRequestElement);
        $this->assertEquals(1543433592, $logoutRequest->getNotOnOrAfter());
    }


    /**
     */
    public function testSetNotOnOrAfter(): void
    {
        $nameId = new NameID('NameIDValue');
        $time = time();

        $logoutRequest = new LogoutRequest($nameId, $time);
        $logoutRequestElement = $logoutRequest->toXML();

        $logoutRequest2 = LogoutRequest::fromXML($logoutRequestElement);
        $this->assertEquals($time, $logoutRequest2->getNotOnOrAfter());
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
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($this->logoutRequestElement);
        $this->assertEquals($reason, $logoutRequest->getReason());
    }


    /**
     */
    public function testSetReason(): void
    {
        $reason = "urn:simplesamlphp:reason-test";
        $nameId = new NameID('NameIDValue');

        $logoutRequest = new LogoutRequest($nameId, null, $reason);
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
  <saml:Issuer>TheIssuer</saml:Issuer>
  <saml:NameID Format="urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified">frits</saml:NameID>
</samlp:LogoutRequest>
XML;
        $document = DOMDocumentFactory::fromString($xml);
        $this->logoutRequestElement = $document->documentElement;

        $logoutRequest = LogoutRequest::fromXML($this->logoutRequestElement);
        $this->assertCount(0, $logoutRequest->getSessionIndexes());
    }


    /**
     */
    public function testSetSessionIndicesVariants(): void
    {
        $nameId = new NameID('test');
        $sessionIndexes = [
            new SessionIndex('SessionIndexValue1'),
            new SessionIndex('SessionIndexValue2'),
        ];

        $logoutRequest = new LogoutRequest(
            identifier: $nameId,
            sessionIndexes: $sessionIndexes,
        );

        $sessionIndexes = $logoutRequest->getSessionIndexes();
        $this->assertCount(2, $sessionIndexes);
        $this->assertEquals('SessionIndexValue1', $sessionIndexes[0]->getContent());
        $this->assertEquals('SessionIndexValue2', $sessionIndexes[1]->getContent());
    }
}
