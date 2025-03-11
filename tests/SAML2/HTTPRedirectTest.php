<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use Exception;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\HTTPRedirect;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;

/**
 * @package simplesamlphp\saml2
 */
#[CoversClass(HTTPRedirect::class)]
final class HTTPRedirectTest extends TestCase
{
    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;

    /** @var \SimpleSAML\SAML2\Compat\AbstractContainer */
    private static AbstractContainer $containerBackup;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$containerBackup = ContainerSingleton::getInstance();

        $container = clone self::$containerBackup;
        $container->setBlacklistedAlgorithms([]);
        ContainerSingleton::setContainer($container);

        self::$clock = $container->getClock();
    }


    /**
     */
    public static function tearDownAfterClass(): void
    {
        ContainerSingleton::setContainer(self::$containerBackup);
    }


    /**
     * test parsing of basic query string with authnrequest and
     * verify that the correct issuer is found.
     */
    public function testRequestParsing(): void
    {
        $q = [
            'SAMLRequest' => 'pVJNjxMxDP0ro9yn88FOtRu1lcpWiEoLVNvCgQtKE6eNlHGG2IHl35OZLmLZQy%2BcrNh%2Bz88vXpDq%2FSDXic%2F4CN8TEBdPvUeSU2EpUkQZFDmSqHogyVru1x8eZDur5RADBx28eAG5jlBEENkFFMV2sxTfzO0dmKa11namPuoc39hba%2BfqpqlbM6%2Fb5mZ%2B1LWtj6L4ApEycikyUYYTJdgisULOqbrpyqYt67tD28iulV33VRSbvI1DxRPqzDyQrCrAk0OYUYpWB4QnnqGvVN4fkJ2emitnhoocnjyU5E5YjnrXf6TfB6TUQ9xD%2FOE0fH58%2BEueHbHOv2Yn1w8eRneqPpiU68M5DxjfdIltqTRNWQNWJc8lDaLYPfv71qHJaq5be7w0kXx%2FOOzK3af9QawWI7ecrIqr%2F9HYAyujWL2SuKheDlhcbuljlrbd7IJ3%2BlfxLsRe8XXlY8aZ0k6tkqNCcvkzsuXeh5%2F3ERTDUnBMIKrVZeS%2FF7v6DQ%3D%3D',
        ];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $hr = new HTTPRedirect();
        $samlrequest = $hr->receive($request);
        $this->assertInstanceOf(AbstractRequest::class, $samlrequest);
        $this->assertEquals(
            'https://profile.surfconext.nl/simplesaml/module.php/saml/sp/metadata.php/default-sp',
            $samlrequest->getIssuer()->getContent(),
        );
    }


    /**
     * test parsing of basic query string with saml response and
     * verify that the correct issuer is found.
     */
    public function testResponseParsing(): void
    {
        $q = [
            'SAMLResponse' => 'vVdbc6rIGn2fX2G5H1MJd0RrJ1UgKmDQIIiXl1NANzcRCA2i%2FvppNLpNJsnMnqo5T9qL%2Fq7r62bxEznbJO%2FNIMqzFMHWfpukqHcCH9tVkfYyB0WolzpbiHql1zNF%2FblHP5C9vMjKzMuS9o3J9xYOQrAooyxtt1T5sd2fzqxplxVIhoIMCbkuIEnehSRJ0xRJdroU1fFc6HWA4Hiw3bJhgbDtYxu7wg4QqqCaotJJSwyRFHdP0fcUb1Fsj2V7pLBut2SIyih1ypNVWJY56hFEGW6iexSBh7y8Z4WHqiweUFX4XpJV4CFNiC1Mkiwl8gyVl57gaOnlv5U9tv9HdzsSTQ4GlCB0hqQ8xD84XYHmOzxFMkOh%2FfSz6UbvlGTxdAkN0yBK4UOJ0zrHzFK4L5ugTlWGMC0j75QsEYEc51E6wCmdn8Stq59ntszSKSv0ftXPAGzZTlLB71lAp909s%2FI8iFCbeDpHeO%2B0J164emN3j6JzD3EddV0%2F1MxDVgQETZIUsdSfTS%2BEW%2Bc%2BOhHSsHWx%2Bnuj22HoMgwA0KV53GHKFQTHcR2PZT0SQEHgfAggECB0%2F8Uw%2FHeMANzLKMBjVhWX0wO%2BKpskyC6B9wAUBT%2FaT3%2B0WhdzCNTUz07e%2Bk6apThwEh1PwXVYhhloiUmQFVEZbr9sKUU2vu%2Fh3rv3KDb9gbnFEX7FOKX4D729y7RAzj0KHerssHE3gz4sIGa6NZ%2Bpj%2B0fv0ffqUyrcFLkZ8UWvV%2F%2BXmow3cEkyyHAZ%2Fqtwmakf8vhp537Sfw1RzkK8KT8mw6%2Bde%2BXk9NBfdou75YRhJU%2FUXO3XN7ZQjh2p%2FmwFsXHUwK3m0%2FAtfHn5YfRubJ8tuhXCeETSyl2wWg%2BX5aA3K4SL6ZhcjciFlLVCUcCKUOKMRcSuwfiRCIPSyXNd5bvktb%2BTkUvuwUi2CWnOgdt42XqgZ75x2dIB0p3bB61VyllUn4Z18mEu6P8TjGtzLkrBfOIOGp70Y6D9apEu1gJkklHFgVlM1TvFra%2FP2SvSubm0kE6slSaB%2FPHazk3%2Bf%2FR1DSGh2t9S47syvgIXhf95pLym1MKn3RV7d8d%2B31xawZirUpioGrii7Z7jg00m7GRLpKjvvk6MlWXkY2BJBlzUR%2BS%2B%2F5R1KRgYkviyhI3nK7PxFoOVrJtGOqgBjZQtGRFh%2BQNrnyBjzFu2bY2crc2xoN6eMblQd0l18sJqUfScbWgkDqaJF5q1EroTXRrXutHldQtA%2F8OmEWDxSdsf8ViCegGqvvGyd9oUGtTSx4YusiORGo%2B6Eu6Yi9nh%2FVikoEbXNp%2FjvdDXZlTtjnbcgnGV7q0OuHiXn8BI%2FsIZDXw6GHp9qV4vdRIXR35H%2Fsn4v5hdxNR7kuRMZYCo78fr4p0IUzqVzCrZ7WjVJjGZ74bGENLc4GfieZzuIog7U6jTDtoNeXfeeIuj1HCmvYq3w0QVGG5VITnIN90xh3mZSNrzwIYBiwaxMMhHwfbuJgOTCbbE0FpgS4g7PpFUYndi%2BqNDhRybY3NB%2Fow4YAwmorHTNtMFuAl7tY2W%2BzYasdwunMQalUWDVHKWKWPayN0iWzqB3JgLCTJslTnpc7HOSZYgKpuHiez9Tw2SzeKcUNqzMGMjCV1pGDbQfDd%2FtdhmA%2BFemkNnnVxc%2BYk1PvWpt4PZHF6nrvAkiib9LZ27CjGDe59gWcYn9jzzbpaL439SBYXZ1y3ZGaWeIxxUJVJ6C7qYEXbB6B%2BPAf1qdZBbQx1UZdEX6hlY6WNs7Ua7ryJaAyGkiHikR6IY2Gws%2Bbkc6BoyKzwhTeF22CW5LnuayKcbqtqPQlNfUUbYbUdTte5I7rCZKjW8%2FHcPhy01Mw6G35TKv2x2mWRgbodnmbp0JLl1aBeaHJXCUUkvk4zmpo7QrC2GIGotzwNmXFQjJe7NIlFd%2FyylJeazjqbY%2BfAK3y926kji%2FdJn4y0hfLKsHFdn2%2BQj5fCFTxfG8TthfLuxnkTCGblxtAr31YTrJ5UuTXEbwCn%2FF5WNUgE7v3T1l7ZvDgiLCDaT6TDsb7LdEm%2Bi2Uiw3DAYSDLkKxP%2BRzPOMDlXZ73%2BTdZcQ75Ppt%2BlvpR47fRY%2BfXz%2FfJeNueC50CFu2vHTUNaU2ycppOC9EvYfEX5dQ9y%2BgZ9KK8qeX%2FLKIvyvSz5D88eqsS7wBR8xg1hUkQkwE%2F04MdXNU%2FqPwihSsQNW9cnH1ZRN45%2FLsnT7%2FZlw9K8urmw%2FpdQOJDhdcUyjBtlDvcYoZap%2BVXSpjucRyu3MSyH3v4sgE03WOZHi382qqmAO4xZZwLuC5Pczzrk5zHeRTPAMahXExcx6V8yDGMA7sQeKB9mx5OusSy%2BhOon%2BBvQixpnr79bPR6XrMPwy%2F4p84KcG3UJ65%2BRbno9zRoVo1YO1yZwpIxxaL%2BwceHFj6k2Y3Hz8w%2BCfgOuzJwRS%2FfT9fPq8vwP%2F0J',
        ];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $hr = new HTTPRedirect();
        $samlrequest = $hr->receive($request);
        $this->assertInstanceOf(Response::class, $samlrequest);
        $issuer = $samlrequest->getIssuer();
        $this->assertEquals('https://engine.test.surfconext.nl/authentication/idp/metadata', $issuer->getContent());
    }


    /**
     * test parsing of Relaystate and SAMLencoding together with authnrequest
     */
    public function testRequestParsingMoreParams(): void
    {
        $q = [
            'SAMLRequest' => 'pVJNb9swDP0rhu6O7XjeGiEJkDYoGqDbgibboZdCkahEgEx5Ir11%2F36y02FdD7n0JPDjPT4%2BcU6q9Z1c9XzCB%2FjRA3H23HokORYWoo8ogyJHElULJFnL3erzvZxOStnFwEEHL15BLiMUEUR2AUW2WS%2FEUw2NrXRp7NWshEPVzJqm%2BTQzVV1DddC21rUy1tq6norsO0RKyIVIRAlO1MMGiRVySpVVk1fTvKr25ZVsGvnh46PI1mkbh4pH1Im5I1kUgEeHMKE%2BWh0QnnmCvlBpf0B2emwunOkKcnj0kJM7Yj7oXf2VfhOQ%2BhbiDuJPp%2BHbw%2F0%2F8uSIdf4tO7m28zC4U7TB9KnendKAIabzO82VpjFrwKrec06dyLYv%2Fl47NEnNZWsP5yaSd%2Fv9Nt9%2B3e3Fcj5wy9GquHyPxhZYGcXqjcR58XrA%2FHxLX5K0zXobvNO%2Fs9sQW8WXlQ8ZZ3I7tkqOCsmlz0iWex9%2B3URQDAvBsQdRLM8j%2F7%2FY5R8%3D',
            'RelayState' => 'https://profile.surfconext.nl/',
            'SAMLEncoding' => 'urn:oasis:names:tc:SAML:2.0:bindings:URL-Encoding:DEFLATE',
        ];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $hr = new HTTPRedirect();
        $samlrequest = $hr->receive($request);
        $this->assertInstanceOf(AbstractRequest::class, $samlrequest);
        $relaystate = $hr->getRelayState();
        $this->assertEquals('https://profile.surfconext.nl/', $relaystate);
    }


    /**
     * Test validating a signed authentication request.
     */
    #[DoesNotPerformAssertions]
    public function testSignedRequestValidation(): void
    {
        $q = [
            'SAMLRequest' => 'hVLLbttADPwVYe%2BylJXsyAvbgBujqIG0MWK3h1wCVkvFC%2BxDXVJp%2B%2FddywmS9uCeCJAznOGACwJne7Ue%2BOjv8ceAxNkvZz2pcbAUQ%2FQqABlSHhyS4lbt159vlZyUqo%2BBQxuseEe5zAAijGyCF9l2sxSPjZ41jW5npZxjpWvsrisoZ9ddV8NU6is5nTczXTfTeS2ybxgpMZciLUp0ogG3nhg8p1Ypq7y8ymV1kJUqa1XJB5Ft0jXGA4%2BsI3NPqiiM7icuhBxiezTPOPG2cEEPFif9sS9OJ5wgBRn%2FZHFvnvyd32N8Ni2KbP1q%2FyZ4GhzGl8nX%2B9s3AY0u%2FE%2BBzlXm0NLY1djBYDmnXmS7l1g%2FGK%2BTi8uJfj%2BDSH06HHb57m5%2FEKvFabcaE4qrV1v%2FOEoOHDJoYFgU7%2FGL80d8SUrbzS5Y0%2F7OPobogC8bOXWMzrsRqjiCJ4OeU2jWhp83EYFxKTgOKIrVWfLvv1v9AQ%3D%3D',
            'RelayState' => 'https%3A%2F%2Fdemo.moo-archive.nl%2Fmodule.php%2Fadmin%2Ftest%2Fdefault-sp',
            'SigAlg' => 'http%3A%2F%2Fwww.w3.org%2F2001%2F04%2Fxmldsig-more%23rsa-sha256',
            'Signature' => 'T8%2BSG10HcgOkpw4cUFTnoF9WWrlYnllnqKruvmVcyinbiJsdnw7EMxM6Lr%2F5Mo%2FRk3Hd7x8tuQ955Vv96jMRKGfdvq8Dh1gx4PKJPHXFWBSipWOc9UDNT0N3addnk9PiSaQ5YehT9lZ4agoSmKqiWNrE4qpKIcgWdh0GgiYDUto%3D',
        ];

        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $hr = new HTTPRedirect();
        $hr->setRelayState($q['RelayState']);
        $samlrequest = $hr->receive($request);
    }


    /**
     * @TODO:  Once we can complete HTTPRedirect and pull certificates from metadata, we can run this test
     * Test validating a signed authentication request.
    public function testSignedRequestValidationWrongKeytype(): void
    {
        $q = [
            'SAMLRequest' => 'fZJRb8IgFIX/SsN7W4qsXYg1cfNhJi4a2+1hLwsCriQFOi41+/mrVTPng0+EezjfvfeEKXDTdmzeh8Zu1XevIEQ/prXARqFEvbfMcdDALDcKWBCsmr+uGEkw67wLTrgWXVnuOziA8kE7i6LlokSfOcUU73P6kHOSUfpY7CZCEiryolC5ollGOOa4oEKi6F15GJwlGkCDHaBXSwuB2zCUMCFxlsV4UhPMsgkjxQeKFsM22vIwupoQOmBpqmWXGOdi7kWjDyqxbXqcmxyFtKrWlfIHLVTSNR2K5pd5n52F3ih/Vt+2qz+iVMbdIo2TfTtCRnoKp5PEXMBYlWrP+zbEMHTZnHN80lZq+3U/wt3pEbCXut7Em3VVo9n0yGZjJH52Get2yS41KnDJA79qPk2vrafb/+8w+wU=',
            'RelayState' => 'https://demo.moo-archive.nl/module.php/admin/test/default-sp',
            'SigAlg' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
            'Signature' => 'Tp/AVn5TblVDZ6qfYLj8Ltk2ZX06kXtMWo5W3oL5WxkoBI/xK/dcTpxjBFMAO3V7g6u2zF4c26gUXdDhAQmrvqTK2oXSmTGdRNYcvwJ0Nkg5i4POcJOaTfZO3p4to8y06RVsmDgvHG0iC3gqhkwu4GjDt1DpPG5AEpk+qZfCm4M=',
        ];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $hr = new HTTPRedirect();
        $request = $hr->receive($request);

        // validate with wrong type of cert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid key type for validating signature');
        $request->validate(PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY));
    }
     */


    /**
     * test that a request with unsupported encoding specified fails
     */
    public function testInvalidEncodingSpecified(): void
    {
        $q = [
            'SAMLRequest' => 'pVJNb9swDP0rhu6O7XjeGiEJkDYoGqDbgibboZdCkahEgEx5Ir11%2F36y02FdD7n0JPDjPT4%2BcU6q9Z1c9XzCB%2FjRA3H23HokORYWoo8ogyJHElULJFnL3erzvZxOStnFwEEHL15BLiMUEUR2AUW2WS%2FEUw2NrXRp7NWshEPVzJqm%2BTQzVV1DddC21rUy1tq6norsO0RKyIVIRAlO1MMGiRVySpVVk1fTvKr25ZVsGvnh46PI1mkbh4pH1Im5I1kUgEeHMKE%2BWh0QnnmCvlBpf0B2emwunOkKcnj0kJM7Yj7oXf2VfhOQ%2BhbiDuJPp%2BHbw%2F0%2F8uSIdf4tO7m28zC4U7TB9KnendKAIabzO82VpjFrwKrec06dyLYv%2Fl47NEnNZWsP5yaSd%2Fv9Nt9%2B3e3Fcj5wy9GquHyPxhZYGcXqjcR58XrA%2FHxLX5K0zXobvNO%2Fs9sQW8WXlQ8ZZ3I7tkqOCsmlz0iWex9%2B3URQDAvBsQdRLM8j%2F7%2FY5R8%3D',
            'RelayState' => 'https://profile.surfconext.nl/',
            'SAMLEncoding' => 'urn:oasis:names:tc:SAML:2.0:bindings:URL-Encoding:none',
        ];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown SAMLEncoding:');
        $hr = new HTTPRedirect();
        $hr->receive($request);
    }


    /**
     */
    public function testNoSigAlgSpecified(): void
    {
        $q = [
            'SAMLRequest' => 'pVJNb9swDP0rhu6O7XjeGiEJkDYoGqDbgibboZdCkahEgEx5Ir11%2F36y02FdD7n0JPDjPT4%2BcU6q9Z1c9XzCB%2FjRA3H23HokORYWoo8ogyJHElULJFnL3erzvZxOStnFwEEHL15BLiMUEUR2AUW2WS%2FEUw2NrXRp7NWshEPVzJqm%2BTQzVV1DddC21rUy1tq6norsO0RKyIVIRAlO1MMGiRVySpVVk1fTvKr25ZVsGvnh46PI1mkbh4pH1Im5I1kUgEeHMKE%2BWh0QnnmCvlBpf0B2emwunOkKcnj0kJM7Yj7oXf2VfhOQ%2BhbiDuJPp%2BHbw%2F0%2F8uSIdf4tO7m28zC4U7TB9KnendKAIabzO82VpjFrwKrec06dyLYv%2Fl47NEnNZWsP5yaSd%2Fv9Nt9%2B3e3Fcj5wy9GquHyPxhZYGcXqjcR58XrA%2FHxLX5K0zXobvNO%2Fs9sQW8WXlQ8ZZ3I7tkqOCsmlz0iWex9%2B3URQDAvBsQdRLM8j%2F7%2FY5R8%3D',
            'RelayState' => 'https://beta.surfnet.nl/simplesaml/module.php/core/authenticate.php?as=Braindrops',
            'Signature' => 'b+qe/XGgICOrEL1v9dwuoy0RJtJ/GNAr7gJGYSJzLG0riPKwo7v5CH8GPC2P9IRikaeaNeQrnhBAaf8FCWrO0cLFw4qR6msK9bxRBGk+hIaTUYCh54ETrVCyGlmBneMgC5/iCRvtEW3ESPXCCqt8Ncu98yZmv9LIVyHSl67Se+fbB9sDw3/fzwYIHRMqK2aS8jnsnqlgnBGGOXqIqN3+d/2dwtCfz14s/9odoYzSUv32qfNPiPez6PSNqwhwH7dWE3TlO/jZmz0DnOeQ2ft6qdZEi5ZN5KCV6VmNKpkrLMq6DDPnuwPm/8oCAoT88R2jG7uf9QZB+ArWJKMEhDLsCA==',
        ];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing signature algorithm');
        $hr = new HTTPRedirect();
        $hr->receive($request);
    }


    /**
     * test handling of non-deflated data in samlrequest
     */
    public function testInvalidRequestData(): void
    {
        $q = ['SAMLRequest' => 'cannotinflate'];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error while base64 decoding SAML message.');
        $hr = new HTTPRedirect();
        @$hr->receive($request);
    }


    /**
     * test with a query string that has Request nor Response
     */
    public function testNoRequestOrResponse(): void
    {
        $q = ['aap' => 'noot', 'mies' => 'jet&wim', 'RelayState' => 'etc'];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing SAMLRequest or SAMLResponse parameter.');
        $hr = new HTTPRedirect();
        $hr->receive($request);
    }


    /**
     * Construct an authnrequest and try to send it without a destination.
     */
    public function testSendWithoutDestination(): void
    {
        $request = new AuthnRequest(self::$clock->now());
        $hr = new HTTPRedirect();
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot build a redirect URL, no destination set.');
        $hr->send($request);
    }


    /**
     * Construct an authnrequest and send it.
     */
    #[DoesNotPerformAssertions]
    public function testSendAuthnrequest(): void
    {
        $request = new AuthnRequest(self::$clock->now());
        $hr = new HTTPRedirect();
        $hr->setDestination('https://idp.example.org/');
        $hr->send($request);
    }


    /**
     * Construct an authnresponse and send it.
     * Also test setting a relaystate and destination for the response.
     */
    #[DoesNotPerformAssertions]
    public function testSendAuthnResponse(): void
    {
        $status = new Status(new StatusCode());
        $issuer = new Issuer('testIssuer');

        $response = new Response(
            status: $status,
            issueInstant: self::$clock->now(),
            issuer: $issuer,
            destination: 'http://example.org/login?success=yes',
        );

        $hr = new HTTPRedirect();
        $hr->setRelayState('http://example.org');
        $hr->send($response);
    }


    /**
     * Test setting destination in the HR binding.
     */
    #[DoesNotPerformAssertions]
    public function testSendAuthnResponseBespokeDestination(): void
    {
        $status = new Status(new StatusCode());
        $issuer = new Issuer('testIssuer');

        $response = new Response($status, self::$clock->now(), $issuer);
        $hr = new HTTPRedirect();
        $hr->setDestination('gopher://myurl');
        $hr->send($response);
    }


    /**
     * Test that providing both SAMLRequest and SAMLResponse is not allowed
     */
    public function testSAMLResponseAndSAMLRequestConfusion(): void
    {
        $q = [
            'SAMLRequest' => 'hVLLbttADPwVYe%2BylJXsyAvbgBujqIG0MWK3h1wCVkvFC%2BxDXVJp%2B%2FddywmS9uCeCJAznOGACwJne7Ue%2BOjv8ceAxNkvZz2pcbAUQ%2FQqABlSHhyS4lbt159vlZyUqo%2BBQxuseEe5zAAijGyCF9l2sxSPjZ41jW5npZxjpWvsrisoZ9ddV8NU6is5nTczXTfTeS2ybxgpMZciLUp0ogG3nhg8p1Ypq7y8ymV1kJUqa1XJB5Ft0jXGA4%2BsI3NPqiiM7icuhBxiezTPOPG2cEEPFif9sS9OJ5wgBRn%2FZHFvnvyd32N8Ni2KbP1q%2FyZ4GhzGl8nX%2B9s3AY0u%2FE%2BBzlXm0NLY1djBYDmnXmS7l1g%2FGK%2BTi8uJfj%2BDSH06HHb57m5%2FEKvFabcaE4qrV1v%2FOEoOHDJoYFgU7%2FGL80d8SUrbzS5Y0%2F7OPobogC8bOXWMzrsRqjiCJ4OeU2jWhp83EYFxKTgOKIrVWfLvv1v9AQ%3D%3D',
            'SAMLResponse' => 'vVdbc6rIGn2fX2G5H1MJd0RrJ1UgKmDQIIiXl1NANzcRCA2i%2FvppNLpNJsnMnqo5T9qL%2Fq7r62bxEznbJO%2FNIMqzFMHWfpukqHcCH9tVkfYyB0WolzpbiHql1zNF%2FblHP5C9vMjKzMuS9o3J9xYOQrAooyxtt1T5sd2fzqxplxVIhoIMCbkuIEnehSRJ0xRJdroU1fFc6HWA4Hiw3bJhgbDtYxu7wg4QqqCaotJJSwyRFHdP0fcUb1Fsj2V7pLBut2SIyih1ypNVWJY56hFEGW6iexSBh7y8Z4WHqiweUFX4XpJV4CFNiC1Mkiwl8gyVl57gaOnlv5U9tv9HdzsSTQ4GlCB0hqQ8xD84XYHmOzxFMkOh%2FfSz6UbvlGTxdAkN0yBK4UOJ0zrHzFK4L5ugTlWGMC0j75QsEYEc51E6wCmdn8Stq59ntszSKSv0ftXPAGzZTlLB71lAp909s%2FI8iFCbeDpHeO%2B0J164emN3j6JzD3EddV0%2F1MxDVgQETZIUsdSfTS%2BEW%2Bc%2BOhHSsHWx%2Bnuj22HoMgwA0KV53GHKFQTHcR2PZT0SQEHgfAggECB0%2F8Uw%2FHeMANzLKMBjVhWX0wO%2BKpskyC6B9wAUBT%2FaT3%2B0WhdzCNTUz07e%2Bk6apThwEh1PwXVYhhloiUmQFVEZbr9sKUU2vu%2Fh3rv3KDb9gbnFEX7FOKX4D729y7RAzj0KHerssHE3gz4sIGa6NZ%2Bpj%2B0fv0ffqUyrcFLkZ8UWvV%2F%2BXmow3cEkyyHAZ%2Fqtwmakf8vhp537Sfw1RzkK8KT8mw6%2Bde%2BXk9NBfdou75YRhJU%2FUXO3XN7ZQjh2p%2FmwFsXHUwK3m0%2FAtfHn5YfRubJ8tuhXCeETSyl2wWg%2BX5aA3K4SL6ZhcjciFlLVCUcCKUOKMRcSuwfiRCIPSyXNd5bvktb%2BTkUvuwUi2CWnOgdt42XqgZ75x2dIB0p3bB61VyllUn4Z18mEu6P8TjGtzLkrBfOIOGp70Y6D9apEu1gJkklHFgVlM1TvFra%2FP2SvSubm0kE6slSaB%2FPHazk3%2Bf%2FR1DSGh2t9S47syvgIXhf95pLym1MKn3RV7d8d%2B31xawZirUpioGrii7Z7jg00m7GRLpKjvvk6MlWXkY2BJBlzUR%2BS%2B%2F5R1KRgYkviyhI3nK7PxFoOVrJtGOqgBjZQtGRFh%2BQNrnyBjzFu2bY2crc2xoN6eMblQd0l18sJqUfScbWgkDqaJF5q1EroTXRrXutHldQtA%2F8OmEWDxSdsf8ViCegGqvvGyd9oUGtTSx4YusiORGo%2B6Eu6Yi9nh%2FVikoEbXNp%2FjvdDXZlTtjnbcgnGV7q0OuHiXn8BI%2FsIZDXw6GHp9qV4vdRIXR35H%2Fsn4v5hdxNR7kuRMZYCo78fr4p0IUzqVzCrZ7WjVJjGZ74bGENLc4GfieZzuIog7U6jTDtoNeXfeeIuj1HCmvYq3w0QVGG5VITnIN90xh3mZSNrzwIYBiwaxMMhHwfbuJgOTCbbE0FpgS4g7PpFUYndi%2BqNDhRybY3NB%2Fow4YAwmorHTNtMFuAl7tY2W%2BzYasdwunMQalUWDVHKWKWPayN0iWzqB3JgLCTJslTnpc7HOSZYgKpuHiez9Tw2SzeKcUNqzMGMjCV1pGDbQfDd%2FtdhmA%2BFemkNnnVxc%2BYk1PvWpt4PZHF6nrvAkiib9LZ27CjGDe59gWcYn9jzzbpaL439SBYXZ1y3ZGaWeIxxUJVJ6C7qYEXbB6B%2BPAf1qdZBbQx1UZdEX6hlY6WNs7Ua7ryJaAyGkiHikR6IY2Gws%2Bbkc6BoyKzwhTeF22CW5LnuayKcbqtqPQlNfUUbYbUdTte5I7rCZKjW8%2FHcPhy01Mw6G35TKv2x2mWRgbodnmbp0JLl1aBeaHJXCUUkvk4zmpo7QrC2GIGotzwNmXFQjJe7NIlFd%2FyylJeazjqbY%2BfAK3y926kji%2FdJn4y0hfLKsHFdn2%2BQj5fCFTxfG8TthfLuxnkTCGblxtAr31YTrJ5UuTXEbwCn%2FF5WNUgE7v3T1l7ZvDgiLCDaT6TDsb7LdEm%2Bi2Uiw3DAYSDLkKxP%2BRzPOMDlXZ73%2BTdZcQ75Ppt%2BlvpR47fRY%2BfXz%2FfJeNueC50CFu2vHTUNaU2ycppOC9EvYfEX5dQ9y%2BgZ9KK8qeX%2FLKIvyvSz5D88eqsS7wBR8xg1hUkQkwE%2F04MdXNU%2FqPwihSsQNW9cnH1ZRN45%2FLsnT7%2FZlw9K8urmw%2FpdQOJDhdcUyjBtlDvcYoZap%2BVXSpjucRyu3MSyH3v4sgE03WOZHi382qqmAO4xZZwLuC5Pczzrk5zHeRTPAMahXExcx6V8yDGMA7sQeKB9mx5OusSy%2BhOon%2BBvQixpnr79bPR6XrMPwy%2F4p84KcG3UJ65%2BRbno9zRoVo1YO1yZwpIxxaL%2BwceHFj6k2Y3Hz8w%2BCfgOuzJwRS%2FfT9fPq8vwP%2F0J',
            'RelayState' => 'etc',
        ];

        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Both SAMLRequest and SAMLResponse provided.');
        $hr = new HTTPRedirect();
        $hr->receive($request);
    }
}
