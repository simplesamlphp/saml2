<?php

namespace SAML2;

use PHPUnit_Framework_Error_Warning;
use PHPUnit_Framework_TestCase;

class HTTPArtifactTest extends PHPUnit_Framework_TestCase
{
    /**
     * The Artifact binding depends on simpleSAMLphp, so currently
     * the only thing we can really unit test is whether the SAMLart
     * parameter is missing.
     */
    public function testArtifactMissingUrlParamThrowsException()
    {
        $_REQUEST = array('a' => 'b', 'c' => 'd');

        $ha = new HTTPArtifact();
        $this->setExpectedException('Exception', 'Missing SAMLart parameter.');
        $request = $ha->receive();
    }
}
