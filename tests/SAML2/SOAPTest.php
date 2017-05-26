<?php

namespace SAML2;

use Exception;
use DOMDocument;

use PHPUnit_Framework_TestCase;

class SOAPTest extends PHPUnit_Framework_TestCase
{
    public function testRequestParsingEmptyMessage()
    {
        $this->setExpectedException('Exception', 'Invalid message received');

        $stub = $this->getStubWithInput('');
        $stub->receive();
    }

    public function testRequestParsing()
    {
        $id = '_6c3a4f8b9c2d';
        $artifact = 'AAQAADWNEw5VT47wcO4zX/iEzMmFQvGknDfws2ZtqSGdkNSbsW1cmVR0bzU=';
        $issuer = 'https://ServiceProvider.com/SAML';

        $input = <<<SOAP
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    <SOAP-ENV:Body>
        <samlp:ArtifactResolve
          xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
          xmlns="urn:oasis:names:tc:SAML:2.0:assertion"
          ID="{$id}" Version="2.0"
          IssueInstant="2004-01-21T19:00:49Z">
            <Issuer>{$issuer}</Issuer>
            <samlp:Artifact>{$artifact}</samlp:Artifact>
        </samlp:ArtifactResolve>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
SOAP;
        $stub = $this->getStubWithInput($input);
        $message = $stub->receive();

        $this->assertInstanceOf('SAML2\\ArtifactResolve', $message);
        $this->assertEquals($artifact, $message->getArtifact());
        $this->assertEquals($id, $message->getId());
        $this->assertEquals($issuer, $message->getIssuer());

        // TODO Validate XML signature is received?
    }

    public function testResponse()
    {
        $xml = <<<SOAP
<samlp:ArtifactResponse
  xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
  xmlns="urn:oasis:names:tc:SAML:2.0:assertion"
  ID="_FQvGknDfws2Z" Version="2.0"
  InResponseTo="_6c3a4f8b9c2d"
  IssueInstant="2004-01-21T19:00:49Z">
    <Issuer>https://IdentityProvider.com/SAML</Issuer>
    <samlp:Status>
        <samlp:StatusCode
          Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
    </samlp:Status>
    <samlp:LogoutResponse ID="_b0730d21b628110d8b7e004005b13a2b"
      InResponseTo="_d2b7c388cec36fa7c39c28fd298644a8"
      IssueInstant="2004-01-21T19:05:49Z"
      Version="2.0">
        <Issuer>https://ServiceProvider.com/SAML</Issuer>
        <samlp:Status>
            <samlp:StatusCode
              Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
        </samlp:Status>
    </samlp:LogoutResponse>
</samlp:ArtifactResponse>
SOAP;
        $doc = new DOMDocument();
        $doc->loadXML($xml);

        $message = Message::fromXML($doc->getElementsByTagName('ArtifactResponse')->item(0));

        $soap = new SOAP();
        $output = $soap->getOutputToSend($message);

        $expected = <<<SOAP
<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body><samlp:ArtifactResponse xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="_FQvGknDfws2Z" Version="2.0" IssueInstant="2004-01-21T19:00:49Z" InResponseTo="_6c3a4f8b9c2d"><saml:Issuer>https://IdentityProvider.com/SAML</saml:Issuer><samlp:Status><samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/></samlp:Status><samlp:LogoutResponse ID="_b0730d21b628110d8b7e004005b13a2b" InResponseTo="_d2b7c388cec36fa7c39c28fd298644a8" IssueInstant="2004-01-21T19:05:49Z" Version="2.0">
        <saml:Issuer>https://ServiceProvider.com/SAML</saml:Issuer>
        <samlp:Status>
            <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
        </samlp:Status>
    </samlp:LogoutResponse></samlp:ArtifactResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>
SOAP;
        $this->assertEquals($output, $expected);
    }

    private function getStubWithInput($input)
    {
        $stub = $this->getMock('SAML2\\SOAP', array('getInputStream'));
        $stub->expects($this->once())
             ->method('getInputStream')
             ->will($this->returnValue($input));

        return $stub;
    }
}
