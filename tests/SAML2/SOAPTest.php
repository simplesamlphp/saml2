<?php

declare(strict_types=1);

namespace SAML2;

use DOMDocument;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use SAML2\XML\samlp\ArtifactResolve;
use SAML2\XML\samlp\MessageFactory;
use SimpleSAML\Assert\Assert;

/**
 * @covers \SAML2\SOAP
 * @package simplesamlphp\saml2
 */
final class SOAPTest extends MockeryTestCase
{
    /**
     * @return void
     */
    public function testRequestParsingEmptyMessage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Argument type: "non-empty string" expected, "string" given');

        $stub = $this->getStubWithInput('');
        $stub->receive();
    }


    /**
     * @return void
     */
    public function testRequestParsing(): void
    {
        $id = '_6c3a4f8b9c2d';
        $artifact = 'AAQAADWNEw5VT47wcO4zX/iEzMmFQvGknDfws2ZtqSGdkNSbsW1cmVR0bzU=';
        $issuer = 'https://ServiceProvider.com/SAML';

        $stub = $this->getStubWithInput(<<<SOAP
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
SOAP
        );

        $message = $stub->receive();

        $this->assertInstanceOf(ArtifactResolve::class, $message);
        $this->assertEquals($artifact, $message->getArtifact());
        $this->assertEquals($id, $message->getId());
        $this->assertEquals($issuer, $message->getIssuer()->getValue());

        // TODO Validate XML signature is received?
    }


    /**
     * @return void
     */
    public function testSendArtifactResponse(): void
    {
        $doc = new DOMDocument();
        $doc->loadXML(<<<XML
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
XML
        );

        $message = MessageFactory::fromXML($doc->documentElement);

        $expected = <<<SOAP
<?xml version="1.0" encoding="utf-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    <SOAP-ENV:Header/>
    <SOAP-ENV:Body><samlp:ArtifactResponse xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" ID="_FQvGknDfws2Z" Version="2.0" IssueInstant="2004-01-21T19:00:49Z" InResponseTo="_6c3a4f8b9c2d"><saml:Issuer>https://IdentityProvider.com/SAML</saml:Issuer><samlp:Status><samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/></samlp:Status><samlp:LogoutResponse ID="_b0730d21b628110d8b7e004005b13a2b" Version="2.0" IssueInstant="2004-01-21T19:05:49Z" InResponseTo="_d2b7c388cec36fa7c39c28fd298644a8"><saml:Issuer>https://ServiceProvider.com/SAML</saml:Issuer><samlp:Status><samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/></samlp:Status></samlp:LogoutResponse></samlp:ArtifactResponse></SOAP-ENV:Body>
</SOAP-ENV:Envelope>

SOAP;

        $soap = new SOAP();
        $output = $soap->getOutputToSend($message);

        $this->assertEquals($expected, $output);
    }


    /**
     * @return void
     */
    public function testSendResponse(): void
    {
        $doc = new DOMDocument();
        $doc->loadXML(<<<XML
<samlp:Response
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="_8e8dc5f69a98cc4c1ff3427e5ce34606fd672f91e6"
    Version="2.0" IssueInstant="2014-07-17T01:01:48Z"
    Destination="http://sp.example.com/demo1/index.php?acs"
    InResponseTo="ONELOGIN_4fee3b046395c4e751011e97f8900b5273d56685">
  <saml:Issuer>http://idp.example.com/metadata.php</saml:Issuer>
  <samlp:Status>
    <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
  </samlp:Status>
  <saml:Assertion
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xmlns:xs="http://www.w3.org/2001/XMLSchema"
      ID="_d71a3a8e9fcc45c9e9d248ef7049393fc8f04e5f75"
      Version="2.0"
      IssueInstant="2014-07-17T01:01:48Z">
    <saml:Issuer>http://idp.example.com/metadata.php</saml:Issuer>
    <saml:Subject>
      <saml:NameID
          Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient"
          SPNameQualifier="http://sp.example.com/demo1/metadata.php">_ce3d2948b4cf20146dee0a0b3dd6f69b6cf86f62d7</saml:NameID>
      <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
        <saml:SubjectConfirmationData
            NotOnOrAfter="2024-01-18T06:21:48Z"
            Recipient="http://sp.example.com/demo1/index.php?acs"
            InResponseTo="ONELOGIN_4fee3b046395c4e751011e97f8900b5273d56685"/>
      </saml:SubjectConfirmation>
    </saml:Subject>
    <saml:Conditions NotBefore="2014-07-17T01:01:18Z" NotOnOrAfter="2024-01-18T06:21:48Z">
      <saml:AudienceRestriction>
        <saml:Audience>http://sp.example.com/demo1/metadata.php</saml:Audience>
      </saml:AudienceRestriction>
    </saml:Conditions>
    <saml:AuthnStatement
        AuthnInstant="2014-07-17T01:01:48Z"
        SessionNotOnOrAfter="2024-07-17T09:01:48Z"
        SessionIndex="_be9967abd904ddcae3c0eb4189adbe3f71e327cf93">
      <saml:AuthnContext>
        <saml:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:Password</saml:AuthnContextClassRef>
      </saml:AuthnContext>
    </saml:AuthnStatement>
    <saml:AttributeStatement>
      <saml:Attribute Name="uid" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:basic">
        <saml:AttributeValue xsi:type="xs:string">test</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="mail" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:basic">
        <saml:AttributeValue xsi:type="xs:string">test@example.com</saml:AttributeValue>
      </saml:Attribute>
      <saml:Attribute Name="eduPersonAffiliation" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:basic">
        <saml:AttributeValue xsi:type="xs:string">users</saml:AttributeValue>
        <saml:AttributeValue xsi:type="xs:string">examplerole1</saml:AttributeValue>
      </saml:Attribute>
    </saml:AttributeStatement>
  </saml:Assertion>
</samlp:Response>
XML
        );

        $message = MessageFactory::fromXML($doc->getElementsByTagName('Response')->item(0));

        $expected = <<<SOAP
<?xml version="1.0" encoding="utf-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    <SOAP-ENV:Header><ecp:Response xmlns:ecp="urn:oasis:names:tc:SAML:2.0:profiles:SSO:ecp" SOAP-ENV:mustUnderstand="1" SOAP-ENV:actor="http://schemas.xmlsoap.org/soap/actor/next" AssertionConsumerServiceURL="http://sp.example.com/demo1/index.php?acs"/></SOAP-ENV:Header>
    <SOAP-ENV:Body><samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ID="_8e8dc5f69a98cc4c1ff3427e5ce34606fd672f91e6" Version="2.0" IssueInstant="2014-07-17T01:01:48Z" Destination="http://sp.example.com/demo1/index.php?acs" InResponseTo="ONELOGIN_4fee3b046395c4e751011e97f8900b5273d56685"><saml:Issuer>http://idp.example.com/metadata.php</saml:Issuer><samlp:Status><samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/></samlp:Status><saml:Assertion xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xs="http://www.w3.org/2001/XMLSchema" ID="_d71a3a8e9fcc45c9e9d248ef7049393fc8f04e5f75" Version="2.0" IssueInstant="2014-07-17T01:01:48Z"><saml:Issuer>http://idp.example.com/metadata.php</saml:Issuer><saml:Subject><saml:NameID SPNameQualifier="http://sp.example.com/demo1/metadata.php" Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">_ce3d2948b4cf20146dee0a0b3dd6f69b6cf86f62d7</saml:NameID><saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer"><saml:SubjectConfirmationData NotOnOrAfter="2024-01-18T06:21:48Z" Recipient="http://sp.example.com/demo1/index.php?acs" InResponseTo="ONELOGIN_4fee3b046395c4e751011e97f8900b5273d56685"/></saml:SubjectConfirmation></saml:Subject><saml:Conditions NotBefore="2014-07-17T01:01:18Z" NotOnOrAfter="2024-01-18T06:21:48Z"><saml:AudienceRestriction><saml:Audience>http://sp.example.com/demo1/metadata.php</saml:Audience></saml:AudienceRestriction></saml:Conditions><saml:AuthnStatement AuthnInstant="2014-07-17T01:01:48Z" SessionNotOnOrAfter="2024-07-17T09:01:48Z" SessionIndex="_be9967abd904ddcae3c0eb4189adbe3f71e327cf93"><saml:AuthnContext><saml:AuthnContextClassRef>urn:oasis:names:tc:SAML:2.0:ac:classes:Password</saml:AuthnContextClassRef></saml:AuthnContext></saml:AuthnStatement><saml:AttributeStatement><saml:Attribute Name="uid" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:basic"><saml:AttributeValue xsi:type="xs:string">test</saml:AttributeValue></saml:Attribute><saml:Attribute Name="mail" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:basic"><saml:AttributeValue xsi:type="xs:string">test@example.com</saml:AttributeValue></saml:Attribute><saml:Attribute Name="eduPersonAffiliation" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:basic"><saml:AttributeValue xsi:type="xs:string">users</saml:AttributeValue><saml:AttributeValue xsi:type="xs:string">examplerole1</saml:AttributeValue></saml:Attribute></saml:AttributeStatement></saml:Assertion></samlp:Response></SOAP-ENV:Body>
</SOAP-ENV:Envelope>

SOAP;

        $soap = new SOAP();
        $output = $soap->getOutputToSend($message);

        $this->assertEquals($expected, $output);
    }


    /**
     * @return SOAP
     */
    private function getStubWithInput($input): SOAP
    {
        $stub = $this->getMockBuilder(SOAP::class)->setMethods(['getInputStream'])->getMock();
        $stub->expects($this->once())
             ->method('getInputStream')
             ->willReturn($input);
        return $stub;
    }
}
