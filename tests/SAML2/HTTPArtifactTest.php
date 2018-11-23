<?php

declare(strict_types=1);

namespace SAML2;

class HTTPArtifactTest extends \PHPUnit\Framework\TestCase
{
    /**
     * The Artifact binding depends on simpleSAMLphp, so currently
     * the only thing we can really unit test is whether the SAMLart
     * parameter is missing.
     */
    public function testArtifactMissingUrlParamThrowsException()
    {
        $_REQUEST = ['a' => 'b', 'c' => 'd'];

        $ha = new HTTPArtifact();
        $this->expectException(\Exception::class, 'Missing SAMLart parameter.');
        $request = $ha->receive();
    }
}
