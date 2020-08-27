<?php

declare(strict_types=1);

namespace SAML2;

use Exception;
use SAML2\HTTPArtifact;

/**
 * @covers \SAML2\HTTPArtifact
 * @package simplesamlphp\saml2
 */
final class HTTPArtifactTest extends \PHPUnit\Framework\TestCase
{
    /**
     * The Artifact binding depends on simpleSAMLphp, so currently
     * the only thing we can really unit test is whether the SAMLart
     * parameter is missing.
     * @return void
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
