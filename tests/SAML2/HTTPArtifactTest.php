<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\HTTPArtifact;

/**
 * @covers \SimpleSAML\SAML2\HTTPArtifact
 * @package simplesamlphp\saml2
 */
final class HTTPArtifactTest extends TestCase
{
    /**
     * The Artifact binding depends on simpleSAMLphp, so currently
     * the only thing we can really unit test is whether the SAMLart
     * parameter is missing.
     */
    public function testArtifactMissingUrlParamThrowsException(): void
    {
        $_REQUEST = ['a' => 'b', 'c' => 'd'];

        $ha = new HTTPArtifact();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing SAMLart parameter.');
        $request = $ha->receive();
    }
}
