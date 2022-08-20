<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\shibmd\Scope;
use SimpleSAML\SAML2\XML\samlp\AbstractResponse;
use SimpleSAML\SAML2\XML\samlp\Extensions;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\SAML2\XML\samlp\StatusMessage;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\AbstractStatusResponseTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractStatusResponse
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class AbstractStatusResponseTest extends TestCase
{
    /**
     */
    public function testMarshalling(): void
    {
        $status = new Status(
            new StatusCode(
                C::STATUS_SUCCESS,
                [
                    new StatusCode(
                        'urn:test:OurSubStatusCode'
                    )
                ]
            ),
            new StatusMessage('OurMessageText')
        );

        $response = new Response($status);

        $responseElement = $response->toXML();

        $xpCache = XPath::getXPath($responseElement);
        $statusElements = XPath::xpQuery($responseElement, './saml_protocol:Status', $xpCache);
        $this->assertCount(1, $statusElements);

        /** @psalm-var \DOMElement[] $statusCodeElements */
        $statusCodeElements = XPath::xpQuery($statusElements[0], './saml_protocol:StatusCode', $xpCache);
        $this->assertCount(1, $statusCodeElements);
        $this->assertEquals(C::STATUS_SUCCESS, $statusCodeElements[0]->getAttribute("Value"));

        /** @psalm-var \DOMElement[] $nestedStatusCodeElements */
        $nestedStatusCodeElements = XPath::xpQuery($statusCodeElements[0], './saml_protocol:StatusCode', $xpCache);
        $this->assertCount(1, $nestedStatusCodeElements);
        $this->assertEquals('urn:test:OurSubStatusCode', $nestedStatusCodeElements[0]->getAttribute("Value"));

        $statusMessageElements = XPath::xpQuery($statusElements[0], './saml_protocol:StatusMessage', $xpCache);
        $this->assertCount(1, $statusMessageElements);
        $this->assertEquals('OurMessageText', $statusMessageElements[0]->textContent);
    }


    /**
     */
    public function testMarshallingSignedResponseElementOrdering(): void
    {
        $status = new Status(
            new StatusCode(
                C::STATUS_SUCCESS,
                [
                    new StatusCode('urn:test:OurSubStatusCode'),
                ]
            ),
            new StatusMessage('OurMessageText')
        );

        $issuer = new Issuer('some issuer');

        $scope = new Scope("scope");

        $extensions = new Extensions([
            new Chunk($scope->toXML()),
        ]);

        $signer = (new SignatureAlgorithmFactory())->getAlgorithm(
            C::SIG_RSA_SHA256,
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY),
        );

        $response = new Response($status, $issuer, null, null, null, null, null, $extensions);
        $response->sign($signer);
        $responseElement = $response->toXML();

        // Test for an Issuer
        $xpCache = XPath::getXPath($responseElement);
        $responseElements = XPath::xpQuery($responseElement, './saml_assertion:Issuer', $xpCache);
        $this->assertCount(1, $responseElements);

        // Test ordering of Response contents
        /** @psalm-var \DOMElement[] $responseElements */
        $responseElements = XPath::xpQuery($responseElement, './saml_assertion:Issuer/following-sibling::*', $xpCache);
        $this->assertCount(3, $responseElements);
        $this->assertEquals('ds:Signature', $responseElements[0]->tagName);
        $this->assertEquals('samlp:Extensions', $responseElements[1]->tagName);
        $this->assertEquals('samlp:Status', $responseElements[2]->tagName);
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $xml = <<<XML
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b"
                InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984"
                Version="2.0"
                IssueInstant="2007-12-10T11:39:48Z"
                Destination="http://somewhere.example.org/simplesaml/saml2/sp/AssertionConsumerService.php">
    <saml:Issuer>max.example.org</saml:Issuer>
    <samlp:Status>
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Responder" />
        <samlp:StatusMessage>Something is wrong...</samlp:StatusMessage>
    </samlp:Status>
</samlp:Response>
XML;

        $fixtureResponseDom = DOMDocumentFactory::fromString($xml);
        $response           = Response::fromXML($fixtureResponseDom->documentElement);

        $this->assertFalse($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:status:Responder", $status->getStatusCode()->getValue());
        $this->assertEmpty($status->getStatusCode()->getSubCodes());
        $this->assertEquals("Something is wrong...", $status->getStatusMessage()->getContent());

        $this->assertEquals("_bec424fa5103428909a30ff1e31168327f79474984", $response->getInResponseTo());
    }


    /**
     * A status reponse that is not an error
     */
    public function testStatusSuccess(): void
    {
        $xml = <<<XML
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b"
                InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984"
                Version="2.0"
                IssueInstant="2007-12-10T11:39:48Z"
                Destination="http://somewhere.example.org/simplesaml/saml2/sp/AssertionConsumerService.php">
    <saml:Issuer>max.example.org</saml:Issuer>
    <samlp:Status>
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success" />
    </samlp:Status>
</samlp:Response>
XML;

        $fixtureResponseDom = DOMDocumentFactory::fromString($xml);
        $response           = Response::fromXML($fixtureResponseDom->documentElement);

        $this->assertTrue($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals(C::STATUS_SUCCESS, $status->getStatusCode()->getValue());
        $this->assertEmpty($status->getStatusCode()->getSubCodes());
        $this->assertNull($status->getStatusMessage());
    }


    /**
     * See if we can parse a StatusResponse with a subcode
     */
    public function testStatusSubcode(): void
    {
        $xml = <<<XML
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="id-HW1q4BUkjB3GuVvTlqYtwr1cuuI-"
                Version="2.0"
                IssueInstant="2016-01-20T20:28:02Z"
                Destination="https://engine.example.edu/authentication/sp/consume-assertion"
                InResponseTo="CORTO8a275030e97351e68e7cc0f89d5b46393d9ee3d9">
  <saml:Issuer>https://example.org/</saml:Issuer>
  <samlp:Status>
    <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Requester">
      <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:RequestDenied"/>
    </samlp:StatusCode>
    <samlp:StatusMessage>The AuthnRequest could not be validated</samlp:StatusMessage>
  </samlp:Status>
</samlp:Response>
XML;

        $fixtureResponseDom = DOMDocumentFactory::fromString($xml);
        $response           = Response::fromXML($fixtureResponseDom->documentElement);

        $this->assertFalse($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals(C::STATUS_REQUESTER, $status->getStatusCode()->getValue());
        $this->assertEquals(C::STATUS_REQUEST_DENIED, $status->getStatusCode()->getSubCodes()[0]->getValue());
        $this->assertEquals("The AuthnRequest could not be validated", $status->getStatusMessage()->getContent());
    }


    /**
     * Test adding in-response-to to a status message.
     */
    public function testResponseTo(): void
    {
        $status = new Status(
            new StatusCode(C::STATUS_REQUESTER)
        );

        $response = new Response($status, null, null, 1453323439, 'aabb12234');

        $responseElement = $response->toXML();

        $expectedStructureDocument = new DOMDocument();
        $expectedStructureDocument->loadXML(<<<STATUSXML
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="123"
                Version="2.0"
                IssueInstant="2016-01-20T20:57:19Z"
                InResponseTo="aabb12234">
  <samlp:Status>
    <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Requester"/>
  </samlp:Status>
</samlp:Response>
STATUSXML
        );
        $expectedStructure = $expectedStructureDocument->documentElement;
        $this->assertEqualXMLStructure($expectedStructure, $responseElement);
    }


    /**
     * A response without any <Status> element throws exception
     */
    public function testNoStatusElementThrowsException(): void
    {
        $this->expectException(MissingElementException::class);

        $xml = <<<XML
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b"
                InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984"
                Version="2.0"
                IssueInstant="2007-12-10T11:39:48Z"
                Destination="http://somewhere.example.org/simplesaml/saml2/sp/AssertionConsumerService.php">
    <saml:Issuer>max.example.org</saml:Issuer>
    <saml:Assertion xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5c"
                    Version="2.0"
                    IssueInstant="2007-12-10T11:39:48Z"
                    >
        <saml:Issuer>max.example.org</saml:Issuer>
     </saml:Assertion>
</samlp:Response>
XML;

        $fixtureResponseDom = DOMDocumentFactory::fromString($xml);
        Response::fromXML($fixtureResponseDom->documentElement);
    }


    /**
     * StatusCode is required in a StatusResponse.
     */
    public function testNoStatusCodeThrowsException(): void
    {
        $this->expectException(MissingElementException::class);

        $xml = <<<XML
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b"
                InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984"
                Version="2.0"
                IssueInstant="2007-12-10T11:39:48Z"
                Destination="http://somewhere.example.org/simplesaml/saml2/sp/AssertionConsumerService.php">
    <saml:Issuer>max.example.org</saml:Issuer>
    <samlp:Status>
        <samlp:StatusMessage>Something is wrong...</samlp:StatusMessage>
    </samlp:Status>
</samlp:Response>
XML;

        $fixtureResponseDom = DOMDocumentFactory::fromString($xml);
        Response::fromXML($fixtureResponseDom->documentElement);
    }
}
