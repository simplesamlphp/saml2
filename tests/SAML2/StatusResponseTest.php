<?php

declare(strict_types=1);

namespace SAML2;

use PHPUnit\Framework\TestCase;
use SAML2\Response;
use SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\StatusResponseTest
 */
class StatusResponseTest extends TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $response = new Response();
        $response->setStatus([
            'Code' => 'OurStatusCode',
            'SubCode' => 'OurSubStatusCode',
            'Message' => 'OurMessageText',
        ]);

        $responseElement = $response->toUnsignedXML();

        $xpCache = XPath::getXPath($responseElement);
        $statusElements = XPath::xpQuery($responseElement, './saml_protocol:Status', $xpCache);
        $this->assertCount(1, $statusElements);

        $xpCache = XPath::getXPath($statusElements[0]);
        $statusCodeElements = XPath::xpQuery($statusElements[0], './saml_protocol:StatusCode', $xpCache);
        $this->assertCount(1, $statusCodeElements);
        $this->assertEquals('OurStatusCode', $statusCodeElements[0]->getAttribute("Value"));

        $statusMessageElements = XPath::xpQuery($statusElements[0], './saml_protocol:StatusMessage', $xpCache);
        $this->assertCount(1, $statusMessageElements);
        $this->assertEquals('OurMessageText', $statusMessageElements[0]->textContent);

        $xpCache = XPath::getXPath($statusCodeElements[0]);
        $nestedStatusCodeElements = XPath::xpQuery($statusCodeElements[0], './saml_protocol:StatusCode', $xpCache);
        $this->assertCount(1, $nestedStatusCodeElements);
        $this->assertEquals('OurSubStatusCode', $nestedStatusCodeElements[0]->getAttribute("Value"));
    }


    /**
     * @return void
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
        $response           = new Response($fixtureResponseDom->firstChild);

        $this->assertFalse($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:status:Responder", $status['Code']);
        $this->assertNull($status['SubCode']);
        $this->assertEquals("Something is wrong...", $status['Message']);

        $this->assertEquals("_bec424fa5103428909a30ff1e31168327f79474984", $response->getInResponseTo());
    }


    /**
     * A status reponse that is not an error
     * @return void
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
        $response           = new Response($fixtureResponseDom->firstChild);

        $this->assertTrue($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:status:Success", $status['Code']);
        $this->assertNull($status['SubCode']);
        $this->assertNull($status['Message']);
    }


    /**
     * See if we can parse a StatusResponse with a subcode
     * @return void
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
        $response           = new Response($fixtureResponseDom->firstChild);

        $this->assertFalse($response->isSuccess());

        $status = $response->getStatus();
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:status:Requester", $status['Code']);
        $this->assertEquals("urn:oasis:names:tc:SAML:2.0:status:RequestDenied", $status['SubCode']);
        $this->assertEquals("The AuthnRequest could not be validated", $status['Message']);
    }


    /**
     * Test adding in-response-to to a status message.
     * @return void
     */
    public function testResponseTo(): void
    {
        $response = new Response();
        $response->setIssueInstant(1453323439);
        $response->setStatus([
            'Code' => 'OurStatusCode'
        ]);
        $response->setInResponseTo('aabb12234');

        $responseStructure = $response->toUnsignedXML();

        // Test for a StatusCode
        $xpCache = XPath::getXPath($responseStructure);
        $statusCodeElements = XPath::xpQuery(
            $responseStructure,
            './saml_protocol:Status/saml_protocol:StatusCode',
            $xpCache,
        );
        $this->assertCount(1, $statusCodeElements);
        $this->assertEquals('OurStatusCode', $statusCodeElements[0]->getAttribute('Value'));
    }


    /**
     * A response without any <Status> element throws exception
     * @return void
     */
    public function testNoStatusElementThrowsException(): void
    {
        $this->expectException(\Exception::class, 'Missing status code on response');

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
        $response           = new Response($fixtureResponseDom->firstChild);
    }


    /**
     * StatusCode is required in a StatusResponse.
     * @return void
     */
    public function testNoStatusCodeThrowsException(): void
    {
        $this->expectException(\Exception::class, 'Missing status code in status element');

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
        $response           = new Response($fixtureResponseDom->firstChild);
    }
}
