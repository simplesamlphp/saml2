<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use Exception;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\HTTPArtifact;

class HTTPArtifactTest extends TestCase
{
    /**
     * The Artifact binding depends on simpleSAMLphp, so currently
     * the only thing we can really unit test is whether the SAMLart
     * parameter is missing.
     * @return void
     */
    public function testArtifactMissingUrlParamThrowsException(): void
    {
        $q = ['a' => 'b', 'c' => 'd'];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $ha = new HTTPArtifact();
        $this->expectException(Exception::class, 'Missing SAMLart parameter.');
        $request = $ha->receive($request);
    }
}
