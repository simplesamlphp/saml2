<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Binding;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use SimpleSAML\SAML2\Binding\SOAP;
use SimpleSAML\SAML2\Exception\Protocol\UnsupportedBindingException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\ecp\RequestAuthenticated;
use SimpleSAML\SAML2\XML\ecp\Response;
use SimpleSAML\SAML2\XML\samlp\ArtifactResolve;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\SOAP11\Constants as C;
use SimpleSAML\SOAP11\Type\MustUnderstandValue;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;

/**
 * @package simplesamlphp\saml2
 */
#[Group('bindings')]
#[CoversClass(SOAP::class)]
final class SOAPTest extends MockeryTestCase
{
    /**
     */
    public function testRequestParsingEmptyMessage(): void
    {
        $this->expectException(UnsupportedBindingException::class);
        $this->expectExceptionMessage('Invalid message received');

        $request = new ServerRequest('', '');
        $stub = $this->getStubWithInput('');
        $stub->receive($request);
    }


    /**
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
        Version="2.0" ID="{$id}"
        IssueInstant="2004-01-21T19:00:49Z">
          <saml:Issuer xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">{$issuer}</saml:Issuer>
          <samlp:Artifact>{$artifact}</samlp:Artifact>
      </samlp:ArtifactResolve>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
SOAP);

        $request = new ServerRequest('', '');
        $message = $stub->receive($request);

        $this->assertInstanceOf(ArtifactResolve::class, $message);
        $this->assertEquals($artifact, $message->getArtifact()->getContent());
        $this->assertEquals($id, $message->getId());
        $this->assertEquals($issuer, $message->getIssuer()->getContent());

        // TODO Validate XML signature is received?
    }


    /**
     */
    public function testSendArtifactResponse(): void
    {
        $artifact = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 3) . '/resources/xml/samlp_ArtifactResponse.xml',
        );
        $message = MessageFactory::fromXML($artifact->documentElement);

        $doc = DOMDocumentFactory::fromString(<<<SOAP
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body /></SOAP-ENV:Envelope>
SOAP);

        /** @var \DOMElement $body */
        $body = $doc->getElementsByTagNameNS(C::NS_SOAP_ENV, 'Body')->item(0);
        $message->toXML($body);

        $soap = new SOAP();
        $actual = $soap->getOutputToSend($message);

        $this->assertEquals($doc->saveXML(), $actual);
    }


    /**
     */
    public function testSendResponse(): void
    {
        $response = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 3) . '/resources/xml/samlp_Response.xml',
        );
        $message = MessageFactory::fromXML($response->documentElement);

        $doc = DOMDocumentFactory::fromString(<<<SOAP
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Header /><SOAP-ENV:Body /></SOAP-ENV:Envelope>
SOAP);
        $requestAuthenticated = new RequestAuthenticated(
            MustUnderstandValue::fromBoolean(true),
        );
        $ecpResponse = new Response(
            SAMLAnyURIValue::fromString('https://example.org/metadata'),
        );


        /** @var \DOMElement $header */
        $header = $doc->getElementsByTagNameNS(C::NS_SOAP_ENV, 'Header')->item(0);
        $requestAuthenticated->toXML($header);
        $ecpResponse->toXML($header);

        /** @var \DOMElement $body */
        $body = $doc->getElementsByTagNameNS(C::NS_SOAP_ENV, 'Body')->item(0);
        $message->toXML($body);

        $soap = new SOAP();
        $actual = $soap->getOutputToSend($message);

        $this->assertEquals($doc->saveXML(), $actual);
    }


    /**
     * @return \SimpleSAML\SAML2\Binding\SOAP
     */
    private function getStubWithInput($input): SOAP
    {
        $stub = $this->getMockBuilder(SOAP::class)->onlyMethods(['getInputStream'])->getMock();
        $stub->expects($this->once())
             ->method('getInputStream')
             ->willReturn($input);
        return $stub;
    }
}
