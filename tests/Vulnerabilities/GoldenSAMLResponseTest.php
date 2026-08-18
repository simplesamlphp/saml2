<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Response;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\CryptoEncoding\PEM;
use SimpleSAML\XMLSecurity\Exception\CanonicalizationFailedException;
use SimpleSAML\XMLSecurity\Key\PublicKey;

use function dirname;

/**
 * @package simplesamlphp/saml2
 */
#[Group('vulnerabilities')]
final class GoldenSAMLResponseTest extends TestCase
{
    /**
     * CVE-2025-66475 / Void Canonicalization ("Golden SAML Response")
     *
     * A relative namespace on an ancestor causes libxml2 C14N to fail.
     * After the fix this must be treated as fatal; the signature must not
     * be accepted (even though DigestValue is the SHA-256 of the empty string).
     */
    public function testGoldenSAMLResponseWithRelativeXmlnsIsRejected(): void
    {
        $doc = DOMDocumentFactory::fromFile(
            dirname(__DIR__, 1) . '/resources/xml/vulnerabilities/CVE-2025-66475.xml',
        );

        $response = Response::fromXML($doc->documentElement);
        $assertion = $response->getAssertions()[0];

        // Key material taken from the Signature in the fixture
        /** @var \SimpleSAML\XMLSecurity\XML\ds\X509Data $x509Data */
        $x509Data = $assertion->getSignature()->getKeyInfo()->getInfo()[0];
        $cert = $x509Data->getData()[0]->getContent()->getValue();

        $alg = $assertion->getSignature()
            ->getSignedInfo()
            ->getSignatureMethod()
            ->getAlgorithm()
            ->getValue();

        $verifier = (new SignatureAlgorithmFactory())->getAlgorithm(
            $alg,
            new PublicKey(
                new PEM(PEM::TYPE_PUBLIC_KEY, $cert),
            ),
        );

        $this->expectException(CanonicalizationFailedException::class);

        @$assertion->verify($verifier);
    }
}
