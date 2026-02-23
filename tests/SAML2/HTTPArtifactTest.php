<?php

declare(strict_types=1);

namespace SAML2;

use SAML2\HTTPArtifact;

class HTTPArtifactTest extends \PHPUnit\Framework\TestCase
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
        $this->expectException(\Exception::class, 'Missing SAMLart parameter.');
        $request = $ha->receive();
    }
}
