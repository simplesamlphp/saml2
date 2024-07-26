<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Binding;

use Exception;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Binding\HTTPArtifact;

/**
 * @package simplesamlphp\saml2
 */
#[CoversClass(HTTPArtifact::class)]
final class HTTPArtifactTest extends TestCase
{
    /**
     * The Artifact binding depends on simpleSAMLphp, so currently
     * the only thing we can really unit test is whether the SAMLart
     * parameter is missing.
     */
    public function testArtifactMissingUrlParamThrowsException(): void
    {
        $q = ['a' => 'b', 'c' => 'd'];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $ha = new HTTPArtifact();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing SAMLart parameter.');
        $ha->receive($request);
    }
}
