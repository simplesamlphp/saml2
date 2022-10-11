<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMDocument;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Nyholm\Psr7\ServerRequest;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Exception\Protocol\UnsupportedBindingException;
use SimpleSAML\SAML2\SOAP;
use SimpleSAML\SAML2\XML\ecp\RequestAuthenticated;
use SimpleSAML\SAML2\XML\ecp\Response;
use SimpleSAML\SAML2\XML\samlp\ArtifactResolve;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\SOAP\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function sprintf;

/**
 * @covers \SimpleSAML\SAML2\SOAP
 * @package simplesamlphp\saml2
 */
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
<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
  <env:Body>
      <samlp:ArtifactResolve
        xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
        xmlns="urn:oasis:names:tc:SAML:2.0:assertion"
        Version="2.0" ID="{$id}"
        IssueInstant="2004-01-21T19:00:49Z">
          <Issuer>{$issuer}</Issuer>
          <samlp:Artifact>{$artifact}</samlp:Artifact>
      </samlp:ArtifactResolve>
  </env:Body>
</env:Envelope>
SOAP
        );

        $request = new ServerRequest('', '');
        $message = $stub->receive($request);

        $this->assertInstanceOf(ArtifactResolve::class, $message);
        $this->assertEquals($artifact, $message->getArtifact());
        $this->assertEquals($id, $message->getId());
        $this->assertEquals($issuer, $message->getIssuer()->getContent());

        // TODO Validate XML signature is received?
    }


    /**
     */
    public function testSendArtifactResponse(): void
    {
        $artifact = DOMDocumentFactory::fromFile(
            dirname(dirname(__FILE__)) . '/resources/xml/samlp_ArtifactResponse.xml'
        );
        $message = MessageFactory::fromXML($artifact->documentElement);

        $doc = DOMDocumentFactory::fromString(<<<SOAP
<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"><env:Body /></env:Envelope>
SOAP
);

        /** @var \DOMElement $body */
        $body = $doc->getElementsByTagNameNS(C::NS_SOAP_ENV_11, 'Body')->item(0);
        $body->appendChild($doc->importNode($message->toXML(), true));

        $soap = new SOAP();
        $actual = $soap->getOutputToSend($message);

        $this->assertEquals($doc->saveXML(), $actual);
    }


    /**
     */
    public function testSendResponse(): void
    {
        $response = DOMDocumentFactory::fromFile(
            dirname(dirname(__FILE__)) . '/resources/xml/samlp_Response.xml'
        );
        $message = MessageFactory::fromXML($response->documentElement);

        $doc = DOMDocumentFactory::fromString(<<<SOAP
<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"><env:Header /><env:Body /></env:Envelope>
SOAP
);
        $requestAuthenticated = new RequestAuthenticated(1);
        $ecpResponse = new Response('https://example.org/metadata');


        /** @var \DOMElement $header */
        $header = $doc->getElementsByTagNameNS(C::NS_SOAP_ENV_11, 'Header')->item(0);
        $header->appendChild($doc->importNode($requestAuthenticated->toXML(), true));
        $header->appendChild($doc->importNode($ecpResponse->toXML(), true));

        /** @var \DOMElement $body */
        $body = $doc->getElementsByTagNameNS(C::NS_SOAP_ENV_11, 'Body')->item(0);
        $body->appendChild($doc->importNode($message->toXML(), true));

        $soap = new SOAP();
        $actual = $soap->getOutputToSend($message);

        $this->assertEquals($doc->saveXML(), $actual);
    }


    /**
     * @return \SimpleSAML\SAML2\SOAP
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
