<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\CryptoEncoding\PEM;
use SimpleSAML\XMLSecurity\Exception\CanonicalizationFailedException;
use SimpleSAML\XMLSecurity\Key\PublicKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function dirname;

/**
 * CVE-2025-66475
 *
 * @package simplesamlphp/saml2
 */
#[Group('vulnerabilities')]
#[CoversClass(ResponseProcessor::class)]
final class GoldenSAMLResponseTest extends TestCase
{
    /**
     */
    public function testSignedResponseWithStrayXmlnsThrowsAnException(): void
    {
        $doc = DOMDocumentFactory::fromFile(
            dirname(__DIR__, 1) . '/resources/xml/vulnerabilities/CVE-2025-66475.xml',
        );

        $response = Response::fromXML($doc->documentElement);
        $assertion = $response->getAssertions()[0];

        $verifier = (new SignatureAlgorithmFactory())->getAlgorithm(
            $assertion->getSignature()->getSignedInfo()->getSignatureMethod()->getAlgorithm()->getValue(),
            new PublicKey(
                new PEM(PEM::TYPE_PUBLIC_KEY, $assertion->getSignature()->getKeyInfo()->getInfo()[0]->getData()[0]->getContent()->getValue()),
            ),
        );

        $this->expectException(CanonicalizationFailedException::class);
        @$assertion->verify($verifier);
    }
}
