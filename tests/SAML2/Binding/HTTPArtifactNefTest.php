<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Binding;

use Dom;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SimpleSAML\Configuration;
use SimpleSAML\SAML2\Binding\HTTPArtifact;
use SimpleSAML\SAML2\XML\samlp\ArtifactResponse;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\Type\SAMLStringValue;

/**
 */
final class HTTPArtifactNefTest extends TestCase
{
    private function idpBMetadata(): Configuration
    {
        // "IdP-B": the federated IdP the attacker controls; its signing cert is in metadata,
        // selected from the SAMLart sourceId. (Uses the same mock cert the shipped test uses.)
        return Configuration::loadFromArray([
            'entityid' => 'https://idp-b.example/',
            'keys' => [[
                'type' => 'X509Certificate',
                'signing' => true,
                'encryption' => false,
                'X509Certificate' => PEMCertificatesMock::getPlainCertificateContents(),
            ]],
        ], '[idp-b]');
    }

    private function call(HTTPArtifact $ha, string $method, ...$args): mixed
    {
        return (new ReflectionMethod(HTTPArtifact::class, $method))->invoke($ha, ...$args);
    }

    public function testArtifactResolveUnsigned(): void
    {
        $idpB = $this->idpBMetadata();
        $ha = new HTTPArtifact();

        // --- The forged embedded <samlp:Response>: UNSIGNED, claims to be from IdP-A. ---
        $embedded = $this->createStub(Response::class);
        $embedded->method('isSigned')->willReturn(false);              // attacker omits the embedded signature
        $embedded->method('getSignature')->willReturn(null);
        $embedded->method('getIssuer')->willReturn(new Issuer(SAMLStringValue::fromString('https://idp-b.example/')));
        // If receive() ever tried to cryptographically verify this, it would blow up here:
        $embedded->method('verify')->willThrowException(
            new Exception('POC-FAIL: embedded Response->verify() was called — short-circuit absent'),
        );
        // --- The OUTER ArtifactResponse: validly signed by IdP-B (the artifact issuer). ---
        $outer = $this->createStub(ArtifactResponse::class);
        $outer->method('isSigned')->willReturn(true);
        $outer->method('isSuccess')->willReturn(true);
        $outer->method('verify')->willReturnCallback(fn() => $outer);  // valid signature under IdP-B's key
        $outer->method('getSignature')->willReturn(self::sigEl());
        $outer->method('getMessage')->willReturn($embedded);

        $query = array();
        $artifactResponse = null;

        // if it is not signed then we will stop right there
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Received SAML Response which is not signed.');
        $reply = $this->call($ha, 'handleReceivedArtifactResponse', $outer,$query,$idpB);
        
    }

    public function testArtifactResolveForImpersonate(): void
    {
        $idpB = $this->idpBMetadata();
        $ha = new HTTPArtifact();

        // --- The forged embedded <samlp:Response>: UNSIGNED, claims to be from IdP-A. ---
        $embedded = $this->createStub(Response::class);
        $embedded->method('isSigned')->willReturn(true);              // attacker omits the embedded signature
        $embedded->method('getSignature')->willReturn(null);
        $embedded->method('getIssuer')->willReturn(new Issuer(SAMLStringValue::fromString('https://idp-a.example/')));
        // If receive() ever tried to cryptographically verify this, it would blow up here:
        $embedded->method('verify')->willThrowException(
            new Exception('POC-FAIL: embedded Response->verify() was called — short-circuit absent'),
        );
        // --- The OUTER ArtifactResponse: validly signed by IdP-B (the artifact issuer). ---
        $outer = $this->createStub(ArtifactResponse::class);
        $outer->method('isSigned')->willReturn(true);
        $outer->method('isSuccess')->willReturn(true);
        $outer->method('verify')->willReturnCallback(fn() => $outer);  // valid signature under IdP-B's key
        $outer->method('getSignature')->willReturn(self::sigEl());
        $outer->method('getMessage')->willReturn($embedded);

        $query = array();
        $artifactResponse = null;

        // if it is signed then we will be rejected because it is not from our entityID
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Received SAML Response from an IdP for another IdP.');
        $reply = $this->call($ha, 'handleReceivedArtifactResponse', $outer,$query,$idpB);
        
    }
    

    private const MINIMAL_SIG =
        '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#"><ds:SignedInfo>'
        . '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>'
        . '<ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>'
        . '<ds:Reference URI="#_x"><ds:Transforms>'
        . '<ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/></ds:Transforms>'
        . '<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><ds:DigestValue>AA==</ds:DigestValue>'
        . '</ds:Reference></ds:SignedInfo><ds:SignatureValue>AA==</ds:SignatureValue></ds:Signature>';

    private static function sigEl(): \SimpleSAML\XMLSecurity\XML\ds\Signature
    {
        $doc = Dom\XMLDocument::createFromString(self::MINIMAL_SIG);
        return \SimpleSAML\XMLSecurity\XML\ds\Signature::fromXML($doc->documentElement);
    }
}

