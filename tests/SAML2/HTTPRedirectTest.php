<?php

/**
 * Class SAML2_HTTPRedirectTest
 */
class SAML2_HTTPRedirectTest extends PHPUnit_Framework_TestCase
{

    /**
     * test parsing of basic query string with authnrequest and
     * verify that the correct issuer is found.
     */
    public function testRequestParsing()
    {
        $request = 'SAMLRequest=pVJNjxMxDP0ro9yn88FOtRu1lcpWiEoLVNvCgQtKE6eNlHGG2IHl35OZLmLZQy%2BcrNh%2Bz88vXpDq%2FSDXic%2F4CN8TEBdPvUeSU2EpUkQZFDmSqHogyVru1x8eZDur5RADBx28eAG5jlBEENkFFMV2sxTfzO0dmKa11namPuoc39hba%2BfqpqlbM6%2Fb5mZ%2B1LWtj6L4ApEycikyUYYTJdgisULOqbrpyqYt67tD28iulV33VRSbvI1DxRPqzDyQrCrAk0OYUYpWB4QnnqGvVN4fkJ2emitnhoocnjyU5E5YjnrXf6TfB6TUQ9xD%2FOE0fH58%2BEueHbHOv2Yn1w8eRneqPpiU68M5DxjfdIltqTRNWQNWJc8lDaLYPfv71qHJaq5be7w0kXx%2FOOzK3af9QawWI7ecrIqr%2F9HYAyujWL2SuKheDlhcbuljlrbd7IJ3%2BlfxLsRe8XXlY8aZ0k6tkqNCcvkzsuXeh5%2F3ERTDUnBMIKrVZeS%2FF7v6DQ%3D%3D';
        $_SERVER['QUERY_STRING'] = $request;

        $hr = new SAML2_HTTPRedirect();
        $request = $hr->receive();
        $this->assertInstanceOf('SAML2_Request', $request);
        $issuer = $request->getIssuer();
        $this->assertEquals('https://profile.surfconext.nl/simplesaml/module.php/saml/sp/metadata.php/default-sp', $issuer);
    }

    /**
     * test parsing of Relaystate and SAMLencoding together with authnrequest
     */
    public function testRequestParsingMoreParams()
    {
        $request = 'SAMLRequest=pVJNb9swDP0rhu6O7XjeGiEJkDYoGqDbgibboZdCkahEgEx5Ir11%2F36y02FdD7n0JPDjPT4%2BcU6q9Z1c9XzCB%2FjRA3H23HokORYWoo8ogyJHElULJFnL3erzvZxOStnFwEEHL15BLiMUEUR2AUW2WS%2FEUw2NrXRp7NWshEPVzJqm%2BTQzVV1DddC21rUy1tq6norsO0RKyIVIRAlO1MMGiRVySpVVk1fTvKr25ZVsGvnh46PI1mkbh4pH1Im5I1kUgEeHMKE%2BWh0QnnmCvlBpf0B2emwunOkKcnj0kJM7Yj7oXf2VfhOQ%2BhbiDuJPp%2BHbw%2F0%2F8uSIdf4tO7m28zC4U7TB9KnendKAIabzO82VpjFrwKrec06dyLYv%2Fl47NEnNZWsP5yaSd%2Fv9Nt9%2B3e3Fcj5wy9GquHyPxhZYGcXqjcR58XrA%2FHxLX5K0zXobvNO%2Fs9sQW8WXlQ8ZZ3I7tkqOCsmlz0iWex9%2B3URQDAvBsQdRLM8j%2F7%2FY5R8%3D&RelayState=https%3A%2F%2Fprofile.surfconext.nl%2F&SAMLEncoding=urn%3Aoasis%3Anames%3Atc%3ASAML%3A2.0%3Abindings%3AURL-Encoding%3ADEFLATE';
        $_SERVER['QUERY_STRING'] = $request;

        $hr = new SAML2_HTTPRedirect();
        $request = $hr->receive();
        $this->assertInstanceOf('SAML2_Request', $request);
        $relaystate = $request->getRelayState();
        $this->assertEquals('https://profile.surfconext.nl/', $relaystate);
    }

    /**
     * Test parsing a signed authentication request.
     * It does not actually verify the validity of the signature.
     */
    public function testSignedRequestParsing()
    {
        $request = 'SAMLRequest=nVLBauMwEP0Vo7sjW7FpKpJA2rBsoNuGOruHXhZFHm8EsuRqxtv27yvbWWgvYelFgjfvzbx5zBJVazu56enkHuG5B6TktbUO5VhYsT446RUalE61gJK0rDY%2F7qSYZbILnrz2ln2QXFYoRAhkvGPJbrtiv7VoygJEoTJ9LOusXDSFuJ4vdH6cxwoIEGUjsrqoFUt%2BQcCoXLHYKMoRe9g5JOUoQlleprlI8%2FyQz6W4ksXiiSXbuI1xikbViahDyfkRSM2wD40DmjnL0bSdhcE6Hx7BTd3xqnqoIPw1GmbdqWPJNx80jCGtGIUeWLL5t8mtd9i3EM78n493%2FzWr9XVvx%2B58mj39IlUaR%2FQmKOPq4Dtkyf4c9E1EjPtzOePjREL5%2FXDYp%2FuH6sDWy6G3HDML66%2B5ayO7VlHx2dySf2y9nM7pPprabffeGv02ZNcquux5QEydNiNVUlAODTiKMVvrX24DKIJz8nw9jfx8tOt3&RelayState=https%3A%2F%2Fbeta.surfnet.nl%2Fsimplesaml%2Fmodule.php%2Fcore%2Fauthenticate.php%3Fas%3DBraindrops&SigAlg=http%3A%2F%2Fwww.w3.org%2F2000%2F09%2Fxmldsig%23rsa-sha1&Signature=b%2Bqe%2FXGgICOrEL1v9dwuoy0RJtJ%2FGNAr7gJGYSJzLG0riPKwo7v5CH8GPC2P9IRikaeaNeQrnhBAaf8FCWrO0cLFw4qR6msK9bxRBGk%2BhIaTUYCh54ETrVCyGlmBneMgC5%2FiCRvtEW3ESPXCCqt8Ncu98yZmv9LIVyHSl67Se%2BfbB9sDw3%2FfzwYIHRMqK2aS8jnsnqlgnBGGOXqIqN3%2Bd%2F2dwtCfz14s%2F9odoYzSUv32qfNPiPez6PSNqwhwH7dWE3TlO%2FjZmz0DnOeQ2ft6qdZEi5ZN5KCV6VmNKpkrLMq6DDPnuwPm%2F8oCAoT88R2jG7uf9QZB%2BArWJKMEhDLsCA%3D%3D';
        $_SERVER['QUERY_STRING'] = $request;

        $hr = new SAML2_HTTPRedirect();
        $request = $hr->receive();
        $this->assertInstanceOf('SAML2_Request', $request);
        $relaystate = $request->getRelayState();
        $this->assertEquals('https://beta.surfnet.nl/simplesaml/module.php/core/authenticate.php?as=Braindrops', $relaystate);
    }

    /**
     * test that a request with unsupported encoding specified fails
     */
    public function testInvalidEncodingSpecified()
    {
        $request = 'SAMLRequest=pVJNb9swDP0rhu6O7XjeGiEJkDYoGqDbgibboZdCkahEgEx5Ir11%2F36y02FdD7n0JPDjPT4%2BcU6q9Z1c9XzCB%2FjRA3H23HokORYWoo8ogyJHElULJFnL3erzvZxOStnFwEEHL15BLiMUEUR2AUW2WS%2FEUw2NrXRp7NWshEPVzJqm%2BTQzVV1DddC21rUy1tq6norsO0RKyIVIRAlO1MMGiRVySpVVk1fTvKr25ZVsGvnh46PI1mkbh4pH1Im5I1kUgEeHMKE%2BWh0QnnmCvlBpf0B2emwunOkKcnj0kJM7Yj7oXf2VfhOQ%2BhbiDuJPp%2BHbw%2F0%2F8uSIdf4tO7m28zC4U7TB9KnendKAIabzO82VpjFrwKrec06dyLYv%2Fl47NEnNZWsP5yaSd%2Fv9Nt9%2B3e3Fcj5wy9GquHyPxhZYGcXqjcR58XrA%2FHxLX5K0zXobvNO%2Fs9sQW8WXlQ8ZZ3I7tkqOCsmlz0iWex9%2B3URQDAvBsQdRLM8j%2F7%2FY5R8%3D&RelayState=https%3A%2F%2Fprofile.surfconext.nl%2F&SAMLEncoding=urn%3Aoasis%3Anames%3Atc%3ASAML%3A2.0%3Abindings%3AURL-Encoding%3Anone';
        $_SERVER['QUERY_STRING'] = $request;

        $this->setExpectedException('Exception', 'Unknown SAMLEncoding:');
        $hr = new SAML2_HTTPRedirect();
        $request = $hr->receive();
    }

    public function testNoSigAlgSpecified()
    {
        $request = 'SAMLRequest=nVLBauMwEP0Vo7sjW7FpKpJA2rBsoNuGOruHXhZFHm8EsuRqxtv27yvbWWgvYelFgjfvzbx5zBJVazu56enkHuG5B6TktbUO5VhYsT446RUalE61gJK0rDY%2F7qSYZbILnrz2ln2QXFYoRAhkvGPJbrtiv7VoygJEoTJ9LOusXDSFuJ4vdH6cxwoIEGUjsrqoFUt%2BQcCoXLHYKMoRe9g5JOUoQlleprlI8%2FyQz6W4ksXiiSXbuI1xikbViahDyfkRSM2wD40DmjnL0bSdhcE6Hx7BTd3xqnqoIPw1GmbdqWPJNx80jCGtGIUeWLL5t8mtd9i3EM78n493%2FzWr9XVvx%2B58mj39IlUaR%2FQmKOPq4Dtkyf4c9E1EjPtzOePjREL5%2FXDYp%2FuH6sDWy6G3HDML66%2B5ayO7VlHx2dySf2y9nM7pPprabffeGv02ZNcquux5QEydNiNVUlAODTiKMVvrX24DKIJz8nw9jfx8tOt3&RelayState=https%3A%2F%2Fbeta.surfnet.nl%2Fsimplesaml%2Fmodule.php%2Fcore%2Fauthenticate.php%3Fas%3DBraindrops&Signature=b%2Bqe%2FXGgICOrEL1v9dwuoy0RJtJ%2FGNAr7gJGYSJzLG0riPKwo7v5CH8GPC2P9IRikaeaNeQrnhBAaf8FCWrO0cLFw4qR6msK9bxRBGk%2BhIaTUYCh54ETrVCyGlmBneMgC5%2FiCRvtEW3ESPXCCqt8Ncu98yZmv9LIVyHSl67Se%2BfbB9sDw3%2FfzwYIHRMqK2aS8jnsnqlgnBGGOXqIqN3%2Bd%2F2dwtCfz14s%2F9odoYzSUv32qfNPiPez6PSNqwhwH7dWE3TlO%2FjZmz0DnOeQ2ft6qdZEi5ZN5KCV6VmNKpkrLMq6DDPnuwPm%2F8oCAoT88R2jG7uf9QZB%2BArWJKMEhDLsCA%3D%3D';
        $_SERVER['QUERY_STRING'] = $request;

        $this->setExpectedException('Exception', 'Missing signature algorithm');
        $hr = new SAML2_HTTPRedirect();
        $request = $hr->receive();
    }

    /**
     * test handling of non-deflated data in samlrequest
     */
    public function testInvalidRequestData()
    {
        $request = 'SAMLRequest=cannotinflate';
        $_SERVER['QUERY_STRING'] = $request;

        $oldwarning = PHPUnit_Framework_Error_Warning::$enabled;
        PHPUnit_Framework_Error_Warning::$enabled = false;
        $this->setExpectedException('Exception', 'Error while inflating');
        $hr = new SAML2_HTTPRedirect();
        $request = @$hr->receive();
        PHPUnit_Framework_Error_Warning::$enabled = $oldwarning;
    }

    /**
     * test with a query string that has Request nor Response
     */
    public function testNoRequestOrResponse()
    {
        $request = 'aap=noot&mies=jet&wim&RelayState=etc';
        $_SERVER['QUERY_STRING'] = $request;

        $this->setExpectedException('Exception', 'Missing SAMLRequest or SAMLResponse parameter.');
        $hr = new SAML2_HTTPRedirect();
        $request = $hr->receive();
    }

    /**
     * Construct an authnrequest and send it.
     */
    public function testSendAuthnrequest()
    {
        $request = new SAML2_AuthnRequest();
        $hr = new SAML2_HTTPRedirect();
	$hr->send($request);
    }

    /**
     * Construct an authnresponse and send it.
     * Also test setting a relaystate and destination for the response.
     */
    public function testSendAuthnResponse()
    {
        $response = new SAML2_Response();
        $response->setIssuer('testIssuer');
        $response->setRelayState('http://example.org');
        $response->setDestination('http://example.org/login?success=yes');
        $hr = new SAML2_HTTPRedirect();
	$hr->send($response);
    }

}
