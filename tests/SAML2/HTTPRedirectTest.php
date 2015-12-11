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
        $request = $hr->receive();
        PHPUnit_Framework_Error_Warning::$enabled = $oldwarning;
    }

    /**
     * test handling of non-deflated data in samlrequest
     */
    public function testNoRequestOrResponse()
    {
        $request = 'aap=noot&mies=jet&wim&RelayState=etc';
        $_SERVER['QUERY_STRING'] = $request;

        $this->setExpectedException('Exception', 'Missing SAMLRequest or SAMLResponse parameter.');
        $hr = new SAML2_HTTPRedirect();
        $request = $hr->receive();
    }
}
