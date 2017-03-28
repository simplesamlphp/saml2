<?php

namespace SAML2;

/**
 * Class \SAML2\StatusResponseTest
 */
class StatusResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $response = new Response();
        $response->setStatus(array(
            'Code' => 'OurStatusCode',
            'SubCode' => 'OurSubStatusCode',
            'Message' => 'OurMessageText',
        ));

        $responseElement = $response->toUnsignedXML();

        $statusElements = Utils::xpQuery($responseElement, './saml_protocol:Status');
        $this->assertCount(1, $statusElements);

        $statusCodeElements = Utils::xpQuery($statusElements[0], './saml_protocol:StatusCode');
        $this->assertCount(1, $statusCodeElements);
        $this->assertEquals('OurStatusCode', $statusCodeElements[0]->getAttribute("Value"));

        $nestedStatusCodeElements = Utils::xpQuery($statusCodeElements[0], './saml_protocol:StatusCode');
        $this->assertCount(1, $nestedStatusCodeElements);
        $this->assertEquals('OurSubStatusCode', $nestedStatusCodeElements[0]->getAttribute("Value"));

        $statusMessageElements = Utils::xpQuery($statusElements[0], './saml_protocol:StatusMessage');
        $this->assertCount(1, $statusMessageElements);
        $this->assertEquals('OurMessageText', $statusMessageElements[0]->textContent);
    }

    public function testUnmarshalling()
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
     */
    public function testStatusSuccess()
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
     */
    public function testStatusSubcode()
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
     */
    public function testResponseTo()
    {
        $response = new Response();
        $response->setIssueInstant(1453323439);
        $response->setStatus(array(
            'Code' => 'OurStatusCode'
        ));
        $response->setInResponseTo('aabb12234');

        $responseElement = $response->toUnsignedXML();

        $expectedStructureDocument = new \DOMDocument();
        $expectedStructureDocument->loadXML(<<<STATUSXML
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="123"
                Version="2.0"
                IssueInstant="2016-01-20T20:57:19Z"
                InResponseTo="aabb12234">
  <samlp:Status>
    <samlp:StatusCode Value="OurStatusCode"/>
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
    public function testNoStatusElementThrowsException()
    {
        $this->setExpectedException('Exception', 'Missing status code on response');

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
     */
    public function testNoStatusCodeThrowsException()
    {
        $this->setExpectedException('Exception', 'Missing status code in status element');

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
