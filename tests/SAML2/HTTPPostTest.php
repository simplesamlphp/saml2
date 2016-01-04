<?php

namespace SAML2;

use PHPUnit_Framework_Error_Warning;
use PHPUnit_Framework_TestCase;

class HTTPPostTest extends PHPUnit_Framework_TestCase
{
    /**
     * test parsing of basic query string with authnrequest and
     * verify that the correct issuer is found.
     */
    public function testRequestParsing()
    {
        $_POST = array();
        $_POST['SAMLRequest'] = 'PHNhbWxwOkF1dGhuUmVxdWVzdCB4bWxuczpzYW1scD0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOnByb3RvY29sIiB4bWxuczpzYW1sPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6YXNzZXJ0aW9uIiBJRD0iQ09SVE9lZjI3YWE5NWM2ZjAzZTMyMjhhZDQyNDU0MWEzMWY1ZWUxMDY5ZmM0IiBWZXJzaW9uPSIyLjAiIElzc3VlSW5zdGFudD0iMjAxNS0xMi0zMVQxMTo1OToyMVoiIERlc3RpbmF0aW9uPSJodHRwczovL3Roa2ktc2lkLnB0LTQ4LnV0ci5zdXJmY2xvdWQubmwvc3NwL3NhbWwyL2lkcC9TU09TZXJ2aWNlLnBocCIgQXNzZXJ0aW9uQ29uc3VtZXJTZXJ2aWNlVVJMPSJodHRwczovL2VuZ2luZS50ZXN0LnN1cmZjb25leHQubmwvYXV0aGVudGljYXRpb24vc3AvY29uc3VtZS1hc3NlcnRpb24iIFByb3RvY29sQmluZGluZz0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmJpbmRpbmdzOkhUVFAtUE9TVCI+PHNhbWw6SXNzdWVyPmh0dHBzOi8vZW5naW5lLnRlc3Quc3VyZmNvbmV4dC5ubC9hdXRoZW50aWNhdGlvbi9zcC9tZXRhZGF0YTwvc2FtbDpJc3N1ZXI+PHNhbWxwOk5hbWVJRFBvbGljeSBBbGxvd0NyZWF0ZT0idHJ1ZSIvPjxzYW1scDpTY29waW5nIFByb3h5Q291bnQ9IjEwIj48c2FtbHA6UmVxdWVzdGVySUQ+aHR0cHM6Ly90aGtpLXNpZC5wdC00OC51dHIuc3VyZmNsb3VkLm5sL21lbGxvbi9tZXRhZGF0YTwvc2FtbHA6UmVxdWVzdGVySUQ+PC9zYW1scDpTY29waW5nPjwvc2FtbHA6QXV0aG5SZXF1ZXN0Pg==';

        $hp = new HTTPPost();
        $request = $hp->receive();
        $this->assertInstanceOf('SAML2\AuthnRequest', $request);
        $issuer = $request->getIssuer();
        $this->assertEquals('https://engine.test.surfconext.nl/authentication/sp/metadata', $issuer);
    }

    /**
     * test parsing of SAMLResponse in post.
     * verify that the correct issuer is found.
     * verify that relaystate is found.
     */
    public function testResponseParsing()
    {
        $_POST = array();
        $_POST['SAMLResponse'] = 'PHNhbWxwOlJlc3BvbnNlIHhtbG5zOnNhbWxwPSJ1cm46b2FzaXM6bmFtZXM6dGM6U0FNTDoyLjA6cHJvdG9jb2wiIHhtbG5zOnNhbWw9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDphc3NlcnRpb24iIElEPSJDT1JUT2JjODA4OGRkNjA2YmQ4ZGQ1NDMwMzQ1ZGIxZjc2YWY1Njk1YzkzMzIiIFZlcnNpb249IjIuMCIgSXNzdWVJbnN0YW50PSIyMDE1LTEyLTMxVDExOjQ4OjEyWiIgRGVzdGluYXRpb249Imh0dHBzOi8vdGhraS1zaWQucHQtNDgudXRyLnN1cmZjbG91ZC5ubC9tZWxsb24vcG9zdFJlc3BvbnNlIiBJblJlc3BvbnNlVG89Il83Mjc1NUI3MkRBQTJDQjY3M0ExQ0YxNURCMkQ1OTA5QiI+PHNhbWw6SXNzdWVyPmh0dHBzOi8vZW5naW5lLnRlc3Quc3VyZmNvbmV4dC5ubC9hdXRoZW50aWNhdGlvbi9pZHAvbWV0YWRhdGE8L3NhbWw6SXNzdWVyPjxzYW1scDpTdGF0dXM+PHNhbWxwOlN0YXR1c0NvZGUgVmFsdWU9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpzdGF0dXM6U3VjY2VzcyIvPjwvc2FtbHA6U3RhdHVzPjxzYW1sOkFzc2VydGlvbiB4bWxuczp4c2k9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvWE1MU2NoZW1hLWluc3RhbmNlIiB4bWxuczp4cz0iaHR0cDovL3d3dy53My5vcmcvMjAwMS9YTUxTY2hlbWEiIElEPSJDT1JUTzNmZjNjNzEzZmM0NGVkMWI5ZWM4MzkzMWEwZWVhNjlmNGRmOWY1MGQiIFZlcnNpb249IjIuMCIgSXNzdWVJbnN0YW50PSIyMDE1LTEyLTMxVDExOjQ4OjEyWiI+PHNhbWw6SXNzdWVyPmh0dHBzOi8vZW5naW5lLnRlc3Quc3VyZmNvbmV4dC5ubC9hdXRoZW50aWNhdGlvbi9pZHAvbWV0YWRhdGE8L3NhbWw6SXNzdWVyPjxkczpTaWduYXR1cmUgeG1sbnM6ZHM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyMiPgogIDxkczpTaWduZWRJbmZvPjxkczpDYW5vbmljYWxpemF0aW9uTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS8xMC94bWwtZXhjLWMxNG4jIi8+CiAgICA8ZHM6U2lnbmF0dXJlTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnI3JzYS1zaGExIi8+CiAgPGRzOlJlZmVyZW5jZSBVUkk9IiNDT1JUTzNmZjNjNzEzZmM0NGVkMWI5ZWM4MzkzMWEwZWVhNjlmNGRmOWY1MGQiPjxkczpUcmFuc2Zvcm1zPjxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjZW52ZWxvcGVkLXNpZ25hdHVyZSIvPjxkczpUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzEwL3htbC1leGMtYzE0biMiLz48L2RzOlRyYW5zZm9ybXM+PGRzOkRpZ2VzdE1ldGhvZCBBbGdvcml0aG09Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvMDkveG1sZHNpZyNzaGExIi8+PGRzOkRpZ2VzdFZhbHVlPkNHekdBNXBKQlN6VkRZOHpTbGplcEhRdEp5ND08L2RzOkRpZ2VzdFZhbHVlPjwvZHM6UmVmZXJlbmNlPjwvZHM6U2lnbmVkSW5mbz48ZHM6U2lnbmF0dXJlVmFsdWU+R2VKVVQ2WjllRENpSTVLVVYzU2FvMHhHL2hnZUI0bk5JcHJvMzYzSElPNm1aYkFMVVBuaVQrS212OFMwOUFDc0ppS0k5MVhidjkvbkVJMGxzVy82aG5teEl6WFFKamU5eTlxcnBhSGtZS203S01oanNRYWhjTWFxMDBVNlNtYnY1RDhNd3ZDak1xWVU3eEVzUmkzejdnTmthT1E1V0Q5dCtUUEMxZGVoc2JnPTwvZHM6U2lnbmF0dXJlVmFsdWU+CjxkczpLZXlJbmZvPjxkczpYNTA5RGF0YT48ZHM6WDUwOUNlcnRpZmljYXRlPk1JSUMrekNDQW1TZ0F3SUJBZ0lKQVBKdkxqUXNSUjRpTUEwR0NTcUdTSWIzRFFFQkJRVUFNRjB4Q3pBSkJnTlZCQVlUQWs1TU1SQXdEZ1lEVlFRSUV3ZFZkSEpsWTJoME1SQXdEZ1lEVlFRSEV3ZFZkSEpsWTJoME1SQXdEZ1lEVlFRS0V3ZFRWVkpHYm1WME1SZ3dGZ1lEVlFRREV3OTBaWE4wTWlCellXMXNJR05sY25Rd0hoY05NVFV3TXpJME1UUXdNekUzV2hjTk1qVXdNekl4TVRRd016RTNXakJkTVFzd0NRWURWUVFHRXdKT1RERVFNQTRHQTFVRUNCTUhWWFJ5WldOb2RERVFNQTRHQTFVRUJ4TUhWWFJ5WldOb2RERVFNQTRHQTFVRUNoTUhVMVZTUm01bGRERVlNQllHQTFVRUF4TVBkR1Z6ZERJZ2MyRnRiQ0JqWlhKME1JR2ZNQTBHQ1NxR1NJYjNEUUVCQVFVQUE0R05BRENCaVFLQmdRQ3hLWXJuVzhOd3FkUndSd2FIdWx1ZUw2OWdRRlRKYmRmb0FTTGhZaWUyYk9pb0p5SncxZitjQXZwanNsNFNWWXB2RXNlSWV0WEg4TGdwazdLNzNQa0RKTDhkRmc0c0VqRkY2amdtanJPRVMzb3gvZ3RUZDlkL1Z3UEhJL3ZQSWNHeTFzYlZKNHBFTUZsNWQ4R09Bem9Ka05XZFBqOXdWNHJ2NHV2MzVNYXk4d0lEQVFBQm80SENNSUcvTUIwR0ExVWREZ1FXQkJUVElhUHdwS3BKbFk4ZUlNU3pOUlpValN0YmlqQ0Jqd1lEVlIwakJJR0hNSUdFZ0JUVElhUHdwS3BKbFk4ZUlNU3pOUlpValN0YmlxRmhwRjh3WFRFTE1Ba0dBMVVFQmhNQ1Rrd3hFREFPQmdOVkJBZ1RCMVYwY21WamFIUXhFREFPQmdOVkJBY1RCMVYwY21WamFIUXhFREFPQmdOVkJBb1RCMU5WVWtadVpYUXhHREFXQmdOVkJBTVREM1JsYzNReUlITmhiV3dnWTJWeWRJSUpBUEp2TGpRc1JSNGlNQXdHQTFVZEV3UUZNQU1CQWY4d0RRWUpLb1pJaHZjTkFRRUZCUUFEZ1lFQUs4RXZUVTBMZ0hKc1N1Z29yT2VtZ1JscHBNZkpBZU9tdXVaTmhTTVkyUWh1bUZPWnBhQWI4TkZJd1VLVVZ5eUpuU283azZrdEhDS0k5NHNRczk3NjI0MmhURERZRXdXSkQ5SGhBc0FxT28yMVVhOGdaVDM4L3dtNjJlM0tncktYdm5sakFiS1BYRFhKTTRha3o3eTZINnd2dklHVDZmMGYwaUpXSHEzNGp3dz08L2RzOlg1MDlDZXJ0aWZpY2F0ZT48L2RzOlg1MDlEYXRhPjwvZHM6S2V5SW5mbz48L2RzOlNpZ25hdHVyZT48c2FtbDpTdWJqZWN0PjxzYW1sOk5hbWVJRCBGb3JtYXQ9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpuYW1laWQtZm9ybWF0OnRyYW5zaWVudCI+ZjhjMWRmMDc0Y2IxMTVhOTllYWNlOGI1OWNlZjVjMzk5YjRhNWFhNDwvc2FtbDpOYW1lSUQ+PHNhbWw6U3ViamVjdENvbmZpcm1hdGlvbiBNZXRob2Q9InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpjbTpiZWFyZXIiPjxzYW1sOlN1YmplY3RDb25maXJtYXRpb25EYXRhIE5vdE9uT3JBZnRlcj0iMjAxNS0xMi0zMVQxMTo1MzoxMloiIFJlY2lwaWVudD0iaHR0cHM6Ly90aGtpLXNpZC5wdC00OC51dHIuc3VyZmNsb3VkLm5sL21lbGxvbi9wb3N0UmVzcG9uc2UiIEluUmVzcG9uc2VUbz0iXzcyNzU1QjcyREFBMkNCNjczQTFDRjE1REIyRDU5MDlCIi8+PC9zYW1sOlN1YmplY3RDb25maXJtYXRpb24+PC9zYW1sOlN1YmplY3Q+PHNhbWw6Q29uZGl0aW9ucyBOb3RCZWZvcmU9IjIwMTUtMTItMzFUMTE6NDg6MTFaIiBOb3RPbk9yQWZ0ZXI9IjIwMTUtMTItMzFUMTE6NTM6MTJaIj48c2FtbDpBdWRpZW5jZVJlc3RyaWN0aW9uPjxzYW1sOkF1ZGllbmNlPmh0dHBzOi8vdGhraS1zaWQucHQtNDgudXRyLnN1cmZjbG91ZC5ubC9tZWxsb24vbWV0YWRhdGE8L3NhbWw6QXVkaWVuY2U+PC9zYW1sOkF1ZGllbmNlUmVzdHJpY3Rpb24+PC9zYW1sOkNvbmRpdGlvbnM+PHNhbWw6QXV0aG5TdGF0ZW1lbnQgQXV0aG5JbnN0YW50PSIyMDE1LTEyLTMxVDExOjQ3OjUwWiI+PHNhbWw6QXV0aG5Db250ZXh0PjxzYW1sOkF1dGhuQ29udGV4dENsYXNzUmVmPnVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDphYzpjbGFzc2VzOlBhc3N3b3JkPC9zYW1sOkF1dGhuQ29udGV4dENsYXNzUmVmPjxzYW1sOkF1dGhlbnRpY2F0aW5nQXV0aG9yaXR5Pmh0dHA6Ly9tb2NrLWlkcDwvc2FtbDpBdXRoZW50aWNhdGluZ0F1dGhvcml0eT48c2FtbDpBdXRoZW50aWNhdGluZ0F1dGhvcml0eT5odHRwOi8vbW9jay1pZHA8L3NhbWw6QXV0aGVudGljYXRpbmdBdXRob3JpdHk+PC9zYW1sOkF1dGhuQ29udGV4dD48L3NhbWw6QXV0aG5TdGF0ZW1lbnQ+PC9zYW1sOkFzc2VydGlvbj48L3NhbWxwOlJlc3BvbnNlPg==';
        $_POST['RelayState'] = 'relaystate001';

        $hp = new HTTPPost();
        $response = $hp->receive();
        $this->assertInstanceOf('SAML2\Response', $response);
        $issuer = $response->getIssuer();
        $this->assertEquals('https://engine.test.surfconext.nl/authentication/idp/metadata', $issuer);
        $relay = $response->getRelayState();
        $this->assertEquals('relaystate001', $relay);
    }


    /**
     * test parsing a request that contains no SAMLRequest or Response
     * must generate an exception.
     */
    public function testNoRequestParsing()
    {
        $_POST = array();
        $_POST = array('non' => 'sense');
        $hp = new HTTPPost();
        $this->setExpectedException('Exception', 'Missing SAMLRequest or SAMLResponse parameter');
        $msg = $hp->receive();
    }

    /**
     * Construct an authnrequest and send it.
     */
    public function testSendAuthnrequest()
    {
        $request = new AuthnRequest();
        $hp = new HTTPPost();
        $hp->send($request);
    }

    /**
     * Construct an authnresponse and send it.
     * Also test setting a relaystate and destination for the response.
     */
    public function testSendAuthnResponse()
    {
        $response = new Response();
        $response->setIssuer('testIssuer');
        $response->setRelayState('http://example.org');
        $response->setDestination('http://example.org/login?success=yes');
        $response->setSignatureKey(CertificatesMock::getPrivateKey());
        $hr = new HTTPPost();
        $hr->send($response);
    }
}
