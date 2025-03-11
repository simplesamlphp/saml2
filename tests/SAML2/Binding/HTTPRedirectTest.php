<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Binding;

use Exception;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Binding\HTTPRedirect;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

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
        $qs = 'SAMLRequest=pVJNjxMxDP0ro9yn88FOtRu1lcpWiEoLVNvCgQtKE6eNlHGG2IHl35OZLmLZQy%2BcrNh%2Bz88vXpDq%2FSDXic%2F4CN8TEBdPvUeSU2EpUkQZFDmSqHogyVru1x8eZDur5RADBx28eAG5jlBEENkFFMV2sxTfzO0dmKa11namPuoc39hba%2BfqpqlbM6%2Fb5mZ%2B1LWtj6L4ApEycikyUYYTJdgisULOqbrpyqYt67tD28iulV33VRSbvI1DxRPqzDyQrCrAk0OYUYpWB4QnnqGvVN4fkJ2emitnhoocnjyU5E5YjnrXf6TfB6TUQ9xD%2FOE0fH58%2BEueHbHOv2Yn1w8eRneqPpiU68M5DxjfdIltqTRNWQNWJc8lDaLYPfv71qHJaq5be7w0kXx%2FOOzK3af9QawWI7ecrIqr%2F9HYAyujWL2SuKheDlhcbuljlrbd7IJ3%2BlfxLsRe8XXlY8aZ0k6tkqNCcvkzsuXeh5%2F3ERTDUnBMIKrVZeS%2FF7v6DQ%3D%3D';
        $_SERVER['QUERY_STRING'] = $qs;

        $q = [
            'SAMLRequest' => 'pVJNjxMxDP0ro9yn88FOtRu1lcpWiEoLVNvCgQtKE6eNlHGG2IHl35OZLmLZQy+crNh+z88vXpDq/SDXic/4CN8TEBdPvUeSU2EpUkQZFDmSqHogyVru1x8eZDur5RADBx28eAG5jlBEENkFFMV2sxTfzO0dmKa11namPuoc39hba+fqpqlbM6/b5mZ+1LWtj6L4ApEycikyUYYTJdgisULOqbrpyqYt67tD28iulV33VRSbvI1DxRPqzDyQrCrAk0OYUYpWB4QnnqGvVN4fkJ2emitnhoocnjyU5E5YjnrXf6TfB6TUQ9xD/OE0fH58+EueHbHOv2Yn1w8eRneqPpiU68M5DxjfdIltqTRNWQNWJc8lDaLYPfv71qHJaq5be7w0kXx/OOzK3af9QawWI7ecrIqr/9HYAyujWL2SuKheDlhcbuljlrbd7IJ3+lfxLsRe8XXlY8aZ0k6tkqNCcvkzsuXeh5/3ERTDUnBMIKrVZeS/F7v6DQ==',
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
        $qs = 'SAMLResponse=vVdbc6rIGn2fX2G5H1MJd0RrJ1UgKmDQIIiXl1NANzcRCA2i%2FvppNLpNJsnMnqo5T9qL%2Fq7r62bxEznbJO%2FNIMqzFMHWfpukqHcCH9tVkfYyB0WolzpbiHql1zNF%2FblHP5C9vMjKzMuS9o3J9xYOQrAooyxtt1T5sd2fzqxplxVIhoIMCbkuIEnehSRJ0xRJdroU1fFc6HWA4Hiw3bJhgbDtYxu7wg4QqqCaotJJSwyRFHdP0fcUb1Fsj2V7pLBut2SIyih1ypNVWJY56hFEGW6iexSBh7y8Z4WHqiweUFX4XpJV4CFNiC1Mkiwl8gyVl57gaOnlv5U9tv9HdzsSTQ4GlCB0hqQ8xD84XYHmOzxFMkOh%2FfSz6UbvlGTxdAkN0yBK4UOJ0zrHzFK4L5ugTlWGMC0j75QsEYEc51E6wCmdn8Stq59ntszSKSv0ftXPAGzZTlLB71lAp909s%2FI8iFCbeDpHeO%2B0J164emN3j6JzD3EddV0%2F1MxDVgQETZIUsdSfTS%2BEW%2Bc%2BOhHSsHWx%2Bnuj22HoMgwA0KV53GHKFQTHcR2PZT0SQEHgfAggECB0%2F8Uw%2FHeMANzLKMBjVhWX0wO%2BKpskyC6B9wAUBT%2FaT3%2B0WhdzCNTUz07e%2Bk6apThwEh1PwXVYhhloiUmQFVEZbr9sKUU2vu%2Fh3rv3KDb9gbnFEX7FOKX4D729y7RAzj0KHerssHE3gz4sIGa6NZ%2Bpj%2B0fv0ffqUyrcFLkZ8UWvV%2F%2BXmow3cEkyyHAZ%2Fqtwmakf8vhp537Sfw1RzkK8KT8mw6%2Bde%2BXk9NBfdou75YRhJU%2FUXO3XN7ZQjh2p%2FmwFsXHUwK3m0%2FAtfHn5YfRubJ8tuhXCeETSyl2wWg%2BX5aA3K4SL6ZhcjciFlLVCUcCKUOKMRcSuwfiRCIPSyXNd5bvktb%2BTkUvuwUi2CWnOgdt42XqgZ75x2dIB0p3bB61VyllUn4Z18mEu6P8TjGtzLkrBfOIOGp70Y6D9apEu1gJkklHFgVlM1TvFra%2FP2SvSubm0kE6slSaB%2FPHazk3%2Bf%2FR1DSGh2t9S47syvgIXhf95pLym1MKn3RV7d8d%2B31xawZirUpioGrii7Z7jg00m7GRLpKjvvk6MlWXkY2BJBlzUR%2BS%2B%2F5R1KRgYkviyhI3nK7PxFoOVrJtGOqgBjZQtGRFh%2BQNrnyBjzFu2bY2crc2xoN6eMblQd0l18sJqUfScbWgkDqaJF5q1EroTXRrXutHldQtA%2F8OmEWDxSdsf8ViCegGqvvGyd9oUGtTSx4YusiORGo%2B6Eu6Yi9nh%2FVikoEbXNp%2FjvdDXZlTtjnbcgnGV7q0OuHiXn8BI%2FsIZDXw6GHp9qV4vdRIXR35H%2Fsn4v5hdxNR7kuRMZYCo78fr4p0IUzqVzCrZ7WjVJjGZ74bGENLc4GfieZzuIog7U6jTDtoNeXfeeIuj1HCmvYq3w0QVGG5VITnIN90xh3mZSNrzwIYBiwaxMMhHwfbuJgOTCbbE0FpgS4g7PpFUYndi%2BqNDhRybY3NB%2Fow4YAwmorHTNtMFuAl7tY2W%2BzYasdwunMQalUWDVHKWKWPayN0iWzqB3JgLCTJslTnpc7HOSZYgKpuHiez9Tw2SzeKcUNqzMGMjCV1pGDbQfDd%2FtdhmA%2BFemkNnnVxc%2BYk1PvWpt4PZHF6nrvAkiib9LZ27CjGDe59gWcYn9jzzbpaL439SBYXZ1y3ZGaWeIxxUJVJ6C7qYEXbB6B%2BPAf1qdZBbQx1UZdEX6hlY6WNs7Ua7ryJaAyGkiHikR6IY2Gws%2Bbkc6BoyKzwhTeF22CW5LnuayKcbqtqPQlNfUUbYbUdTte5I7rCZKjW8%2FHcPhy01Mw6G35TKv2x2mWRgbodnmbp0JLl1aBeaHJXCUUkvk4zmpo7QrC2GIGotzwNmXFQjJe7NIlFd%2FyylJeazjqbY%2BfAK3y926kji%2FdJn4y0hfLKsHFdn2%2BQj5fCFTxfG8TthfLuxnkTCGblxtAr31YTrJ5UuTXEbwCn%2FF5WNUgE7v3T1l7ZvDgiLCDaT6TDsb7LdEm%2Bi2Uiw3DAYSDLkKxP%2BRzPOMDlXZ73%2BTdZcQ75Ppt%2BlvpR47fRY%2BfXz%2FfJeNueC50CFu2vHTUNaU2ycppOC9EvYfEX5dQ9y%2BgZ9KK8qeX%2FLKIvyvSz5D88eqsS7wBR8xg1hUkQkwE%2F04MdXNU%2FqPwihSsQNW9cnH1ZRN45%2FLsnT7%2FZlw9K8urmw%2FpdQOJDhdcUyjBtlDvcYoZap%2BVXSpjucRyu3MSyH3v4sgE03WOZHi382qqmAO4xZZwLuC5Pczzrk5zHeRTPAMahXExcx6V8yDGMA7sQeKB9mx5OusSy%2BhOon%2BBvQixpnr79bPR6XrMPwy%2F4p84KcG3UJ65%2BRbno9zRoVo1YO1yZwpIxxaL%2BwceHFj6k2Y3Hz8w%2BCfgOuzJwRS%2FfT9fPq8vwP%2F0J';
        $_SERVER['QUERY_STRING'] = $qs;

        $q = [
            'SAMLResponse' => 'vVdbc6rIGn2fX2G5H1MJd0RrJ1UgKmDQIIiXl1NANzcRCA2i/vppNLpNJsnMnqo5T9qL/q7r62bxEznbJO/NIMqzFMHWfpukqHcCH9tVkfYyB0WolzpbiHql1zNF/blHP5C9vMjKzMuS9o3J9xYOQrAooyxtt1T5sd2fzqxplxVIhoIMCbkuIEnehSRJ0xRJdroU1fFc6HWA4Hiw3bJhgbDtYxu7wg4QqqCaotJJSwyRFHdP0fcUb1Fsj2V7pLBut2SIyih1ypNVWJY56hFEGW6iexSBh7y8Z4WHqiweUFX4XpJV4CFNiC1Mkiwl8gyVl57gaOnlv5U9tv9HdzsSTQ4GlCB0hqQ8xD84XYHmOzxFMkOh/fSz6UbvlGTxdAkN0yBK4UOJ0zrHzFK4L5ugTlWGMC0j75QsEYEc51E6wCmdn8Stq59ntszSKSv0ftXPAGzZTlLB71lAp909s/I8iFCbeDpHeO+0J164emN3j6JzD3EddV0/1MxDVgQETZIUsdSfTS+EW+c+OhHSsHWx+nuj22HoMgwA0KV53GHKFQTHcR2PZT0SQEHgfAggECB0/8Uw/HeMANzLKMBjVhWX0wO+KpskyC6B9wAUBT/aT3+0WhdzCNTUz07e+k6apThwEh1PwXVYhhloiUmQFVEZbr9sKUU2vu/h3rv3KDb9gbnFEX7FOKX4D729y7RAzj0KHerssHE3gz4sIGa6NZ+pj+0fv0ffqUyrcFLkZ8UWvV/+Xmow3cEkyyHAZ/qtwmakf8vhp537Sfw1RzkK8KT8mw6+de+Xk9NBfdou75YRhJU/UXO3XN7ZQjh2p/mwFsXHUwK3m0/AtfHn5YfRubJ8tuhXCeETSyl2wWg+X5aA3K4SL6ZhcjciFlLVCUcCKUOKMRcSuwfiRCIPSyXNd5bvktb+TkUvuwUi2CWnOgdt42XqgZ75x2dIB0p3bB61VyllUn4Z18mEu6P8TjGtzLkrBfOIOGp70Y6D9apEu1gJkklHFgVlM1TvFra/P2SvSubm0kE6slSaB/PHazk3+f/R1DSGh2t9S47syvgIXhf95pLym1MKn3RV7d8d+31xawZirUpioGrii7Z7jg00m7GRLpKjvvk6MlWXkY2BJBlzUR+S+/5R1KRgYkviyhI3nK7PxFoOVrJtGOqgBjZQtGRFh+QNrnyBjzFu2bY2crc2xoN6eMblQd0l18sJqUfScbWgkDqaJF5q1EroTXRrXutHldQtA/8OmEWDxSdsf8ViCegGqvvGyd9oUGtTSx4YusiORGo+6Eu6Yi9nh/VikoEbXNp/jvdDXZlTtjnbcgnGV7q0OuHiXn8BI/sIZDXw6GHp9qV4vdRIXR35H/sn4v5hdxNR7kuRMZYCo78fr4p0IUzqVzCrZ7WjVJjGZ74bGENLc4GfieZzuIog7U6jTDtoNeXfeeIuj1HCmvYq3w0QVGG5VITnIN90xh3mZSNrzwIYBiwaxMMhHwfbuJgOTCbbE0FpgS4g7PpFUYndi+qNDhRybY3NB/ow4YAwmorHTNtMFuAl7tY2W+zYasdwunMQalUWDVHKWKWPayN0iWzqB3JgLCTJslTnpc7HOSZYgKpuHiez9Tw2SzeKcUNqzMGMjCV1pGDbQfDd/tdhmA+FemkNnnVxc+Yk1PvWpt4PZHF6nrvAkiib9LZ27CjGDe59gWcYn9jzzbpaL439SBYXZ1y3ZGaWeIxxUJVJ6C7qYEXbB6B+PAf1qdZBbQx1UZdEX6hlY6WNs7Ua7ryJaAyGkiHikR6IY2Gws+bkc6BoyKzwhTeF22CW5LnuayKcbqtqPQlNfUUbYbUdTte5I7rCZKjW8/HcPhy01Mw6G35TKv2x2mWRgbodnmbp0JLl1aBeaHJXCUUkvk4zmpo7QrC2GIGotzwNmXFQjJe7NIlFd/yylJeazjqbY+fAK3y926kji/dJn4y0hfLKsHFdn2+Qj5fCFTxfG8TthfLuxnkTCGblxtAr31YTrJ5UuTXEbwCn/F5WNUgE7v3T1l7ZvDgiLCDaT6TDsb7LdEm+i2Uiw3DAYSDLkKxP+RzPOMDlXZ73+TdZcQ75Ppt+lvpR47fRY+fXz/fJeNueC50CFu2vHTUNaU2ycppOC9EvYfEX5dQ9y+gZ9KK8qeX/LKIvyvSz5D88eqsS7wBR8xg1hUkQkwE/04MdXNU/qPwihSsQNW9cnH1ZRN45/LsnT7/Zlw9K8urmw/pdQOJDhdcUyjBtlDvcYoZap+VXSpjucRyu3MSyH3v4sgE03WOZHi382qqmAO4xZZwLuC5Pczzrk5zHeRTPAMahXExcx6V8yDGMA7sQeKB9mx5OusSy+hOon+BvQixpnr79bPR6XrMPwy/4p84KcG3UJ65+Rbno9zRoVo1YO1yZwpIxxaL+wceHFj6k2Y3Hz8w+CfgOuzJwRS/fT9fPq8vwP/0J',
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
        $request = 'SAMLRequest=pVJNb9swDP0rhu6O7XjeGiEJkDYoGqDbgibboZdCkahEgEx5Ir11%2F36y02FdD7n0JPDjPT4%2BcU6q9Z1c9XzCB%2FjRA3H23HokORYWoo8ogyJHElULJFnL3erzvZxOStnFwEEHL15BLiMUEUR2AUW2WS%2FEUw2NrXRp7NWshEPVzJqm%2BTQzVV1DddC21rUy1tq6norsO0RKyIVIRAlO1MMGiRVySpVVk1fTvKr25ZVsGvnh46PI1mkbh4pH1Im5I1kUgEeHMKE%2BWh0QnnmCvlBpf0B2emwunOkKcnj0kJM7Yj7oXf2VfhOQ%2BhbiDuJPp%2BHbw%2F0%2F8uSIdf4tO7m28zC4U7TB9KnendKAIabzO82VpjFrwKrec06dyLYv%2Fl47NEnNZWsP5yaSd%2Fv9Nt9%2B3e3Fcj5wy9GquHyPxhZYGcXqjcR58XrA%2FHxLX5K0zXobvNO%2Fs9sQW8WXlQ8ZZ3I7tkqOCsmlz0iWex9%2B3URQDAvBsQdRLM8j%2F7%2FY5R8%3D&RelayState=https%3A%2F%2Fprofile.surfconext.nl%2F&SAMLEncoding=urn%3Aoasis%3Anames%3Atc%3ASAML%3A2.0%3Abindings%3AURL-Encoding%3ADEFLATE';
        $_SERVER['QUERY_STRING'] = $request;

        $q = [
            'SAMLRequest' => 'pVJNb9swDP0rhu6O7XjeGiEJkDYoGqDbgibboZdCkahEgEx5Ir11/36y02FdD7n0JPDjPT4+cU6q9Z1c9XzCB/jRA3H23HokORYWoo8ogyJHElULJFnL3erzvZxOStnFwEEHL15BLiMUEUR2AUW2WS/EUw2NrXRp7NWshEPVzJqm+TQzVV1DddC21rUy1tq6norsO0RKyIVIRAlO1MMGiRVySpVVk1fTvKr25ZVsGvnh46PI1mkbh4pH1Im5I1kUgEeHMKE+Wh0QnnmCvlBpf0B2emwunOkKcnj0kJM7Yj7oXf2VfhOQ+hbiDuJPp+Hbw/0/8uSIdf4tO7m28zC4U7TB9KnendKAIabzO82VpjFrwKrec06dyLYv/l47NEnNZWsP5yaSd/v9Nt9+3e3Fcj5wy9GquHyPxhZYGcXqjcR58XrA/HxLX5K0zXobvNO/s9sQW8WXlQ8ZZ3I7tkqOCsmlz0iWex9+3URQDAvBsQdRLM8j/7/Y5R8=',
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
     * Test parsing a signed authentication request.
     * It does not actually verify the validity of the signature.
     */
    public function testSignedRequestParsing(): void
    {
        $qs = 'SAMLRequest=hVLLbttADPwVYe%2BylJXsyAvbgBujqIG0MWK3h1wCVkvFC%2BxDXVJp%2B%2FddywmS9uCeCJAznOGACwJne7Ue%2BOjv8ceAxNkvZz2pcbAUQ%2FQqABlSHhyS4lbt159vlZyUqo%2BBQxuseEe5zAAijGyCF9l2sxSPjZ41jW5npZxjpWvsrisoZ9ddV8NU6is5nTczXTfTeS2ybxgpMZciLUp0ogG3nhg8p1Ypq7y8ymV1kJUqa1XJB5Ft0jXGA4%2BsI3NPqiiM7icuhBxiezTPOPG2cEEPFif9sS9OJ5wgBRn%2FZHFvnvyd32N8Ni2KbP1q%2FyZ4GhzGl8nX%2B9s3AY0u%2FE%2BBzlXm0NLY1djBYDmnXmS7l1g%2FGK%2BTi8uJfj%2BDSH06HHb57m5%2FEKvFabcaE4qrV1v%2FOEoOHDJoYFgU7%2FGL80d8SUrbzS5Y0%2F7OPobogC8bOXWMzrsRqjiCJ4OeU2jWhp83EYFxKTgOKIrVWfLvv1v9AQ%3D%3D&RelayState=https%3A%2F%2Fdemo.moo-archive.nl%2Fmodule.php%2Fadmin%2Ftest%2Fdefault-sp&SigAlg=http%3A%2F%2Fwww.w3.org%2F2001%2F04%2Fxmldsig-more%23rsa-sha256&Signature=T8%2BSG10HcgOkpw4cUFTnoF9WWrlYnllnqKruvmVcyinbiJsdnw7EMxM6Lr%2F5Mo%2FRk3Hd7x8tuQ955Vv96jMRKGfdvq8Dh1gx4PKJPHXFWBSipWOc9UDNT0N3addnk9PiSaQ5YehT9lZ4agoSmKqiWNrE4qpKIcgWdh0GgiYDUto%3D';
        $_SERVER['QUERY_STRING'] = $qs;

        $q = [
            'SAMLRequest' => 'hVLLbttADPwVYe+ylJXsyAvbgBujqIG0MWK3h1wCVkvFC+xDXVJp+/ddywmS9uCeCJAznOGACwJne7Ue+Ojv8ceAxNkvZz2pcbAUQ/QqABlSHhyS4lbt159vlZyUqo+BQxuseEe5zAAijGyCF9l2sxSPjZ41jW5npZxjpWvsrisoZ9ddV8NU6is5nTczXTfTeS2ybxgpMZciLUp0ogG3nhg8p1Ypq7y8ymV1kJUqa1XJB5Ft0jXGA4+sI3NPqiiM7icuhBxiezTPOPG2cEEPFif9sS9OJ5wgBRn/ZHFvnvyd32N8Ni2KbP1q/yZ4GhzGl8nX+9s3AY0u/E+BzlXm0NLY1djBYDmnXmS7l1g/GK+Ti8uJfj+DSH06HHb57m5/EKvFabcaE4qrV1v/OEoOHDJoYFgU7/GL80d8SUrbzS5Y0/7OPobogC8bOXWMzrsRqjiCJ4OeU2jWhp83EYFxKTgOKIrVWfLvv1v9AQ==',
            'RelayState' => 'https://demo.moo-archive.nl/module.php/admin/test/default-sp',
            'SigAlg' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
            'Signature' => 'T8+SG10HcgOkpw4cUFTnoF9WWrlYnllnqKruvmVcyinbiJsdnw7EMxM6Lr/5Mo/Rk3Hd7x8tuQ955Vv96jMRKGfdvq8Dh1gx4PKJPHXFWBSipWOc9UDNT0N3addnk9PiSaQ5YehT9lZ4agoSmKqiWNrE4qpKIcgWdh0GgiYDUto=',
        ];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $hr = new HTTPRedirect();
        $samlrequest = $hr->receive($request);
        $this->assertInstanceOf(AbstractRequest::class, $samlrequest);
        $relaystate = $hr->getRelayState();
        $this->assertEquals(
            'https://demo.moo-archive.nl/module.php/admin/test/default-sp',
            $relaystate,
        );
    }


    /**
     * Test validating a signed authentication request.
     */
    public function testSignedRequestValidation(): void
    {
        $qs = 'SAMLRequest=hVLLbttADPwVYe%2BylJXsyAvbgBujqIG0MWK3h1wCVkvFC%2BxDXVJp%2B%2FddywmS9uCeCJAznOGACwJne7Ue%2BOjv8ceAxNkvZz2pcbAUQ%2FQqABlSHhyS4lbt159vlZyUqo%2BBQxuseEe5zAAijGyCF9l2sxSPjZ41jW5npZxjpWvsrisoZ9ddV8NU6is5nTczXTfTeS2ybxgpMZciLUp0ogG3nhg8p1Ypq7y8ymV1kJUqa1XJB5Ft0jXGA4%2BsI3NPqiiM7icuhBxiezTPOPG2cEEPFif9sS9OJ5wgBRn%2FZHFvnvyd32N8Ni2KbP1q%2FyZ4GhzGl8nX%2B9s3AY0u%2FE%2BBzlXm0NLY1djBYDmnXmS7l1g%2FGK%2BTi8uJfj%2BDSH06HHb57m5%2FEKvFabcaE4qrV1v%2FOEoOHDJoYFgU7%2FGL80d8SUrbzS5Y0%2F7OPobogC8bOXWMzrsRqjiCJ4OeU2jWhp83EYFxKTgOKIrVWfLvv1v9AQ%3D%3D&RelayState=https%3A%2F%2Fdemo.moo-archive.nl%2Fmodule.php%2Fadmin%2Ftest%2Fdefault-sp&SigAlg=http%3A%2F%2Fwww.w3.org%2F2001%2F04%2Fxmldsig-more%23rsa-sha256&Signature=T8%2BSG10HcgOkpw4cUFTnoF9WWrlYnllnqKruvmVcyinbiJsdnw7EMxM6Lr%2F5Mo%2FRk3Hd7x8tuQ955Vv96jMRKGfdvq8Dh1gx4PKJPHXFWBSipWOc9UDNT0N3addnk9PiSaQ5YehT9lZ4agoSmKqiWNrE4qpKIcgWdh0GgiYDUto%3D';
        $_SERVER['QUERY_STRING'] = $qs;

        $q = [
            'SAMLRequest' => 'hVLLbttADPwVYe+ylJXsyAvbgBujqIG0MWK3h1wCVkvFC+xDXVJp+/ddywmS9uCeCJAznOGACwJne7Ue+Ojv8ceAxNkvZz2pcbAUQ/QqABlSHhyS4lbt159vlZyUqo+BQxuseEe5zAAijGyCF9l2sxSPjZ41jW5npZxjpWvsrisoZ9ddV8NU6is5nTczXTfTeS2ybxgpMZciLUp0ogG3nhg8p1Ypq7y8ymV1kJUqa1XJB5Ft0jXGA4+sI3NPqiiM7icuhBxiezTPOPG2cEEPFif9sS9OJ5wgBRn/ZHFvnvyd32N8Ni2KbP1q/yZ4GhzGl8nX+9s3AY0u/E+BzlXm0NLY1djBYDmnXmS7l1g/GK+Ti8uJfj+DSH06HHb57m5/EKvFabcaE4qrV1v/OEoOHDJoYFgU7/GL80d8SUrbzS5Y0/7OPobogC8bOXWMzrsRqjiCJ4OeU2jWhp83EYFxKTgOKIrVWfLvv1v9AQ==',
            'RelayState' => 'https://demo.moo-archive.nl/module.php/admin/test/default-sp',
            'SigAlg' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
            'Signature' => 'T8+SG10HcgOkpw4cUFTnoF9WWrlYnllnqKruvmVcyinbiJsdnw7EMxM6Lr/5Mo/Rk3Hd7x8tuQ955Vv96jMRKGfdvq8Dh1gx4PKJPHXFWBSipWOc9UDNT0N3addnk9PiSaQ5YehT9lZ4agoSmKqiWNrE4qpKIcgWdh0GgiYDUto=',
        ];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $hr = new HTTPRedirect();
        $hr->setRelayState(urlencode($q['RelayState']));
        $samlrequest = $hr->receive($request);

        // validate with the correct certificate, should verify
        $verifier = (new SignatureAlgorithmFactory())->getAlgorithm(
            $q['SigAlg'],
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
        );

        $signedQuery = 'SAMLRequest=' . urlencode($q['SAMLRequest']);
        $signedQuery .= '&RelayState=' . urlencode($q['RelayState']);
        $signedQuery .= '&SigAlg=' . urlencode($q['SigAlg']);
        $this->assertTrue($verifier->verify($signedQuery, base64_decode($q['Signature'], true)));

        // validate with another cert, should fail
        $verifier = (new SignatureAlgorithmFactory())->getAlgorithm(
            $q['SigAlg'],
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::OTHER_PUBLIC_KEY),
        );

        $signedQuery = 'SAMLRequest=' . urlencode($q['SAMLRequest']);
        $signedQuery .= '&RelayState=' . urlencode($q['RelayState']);
        $signedQuery .= '&SigAlg=' . urlencode($q['SigAlg']);
        $this->assertFalse($verifier->verify($signedQuery, base64_decode($q['Signature'], true)));
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
        $qs = 'SAMLRequest=pVJNb9swDP0rhu6O7XjeGiEJkDYoGqDbgibboZdCkahEgEx5Ir11%2F36y02FdD7n0JPDjPT4%2BcU6q9Z1c9XzCB%2FjRA3H23HokORYWoo8ogyJHElULJFnL3erzvZxOStnFwEEHL15BLiMUEUR2AUW2WS%2FEUw2NrXRp7NWshEPVzJqm%2BTQzVV1DddC21rUy1tq6norsO0RKyIVIRAlO1MMGiRVySpVVk1fTvKr25ZVsGvnh46PI1mkbh4pH1Im5I1kUgEeHMKE%2BWh0QnnmCvlBpf0B2emwunOkKcnj0kJM7Yj7oXf2VfhOQ%2BhbiDuJPp%2BHbw%2F0%2F8uSIdf4tO7m28zC4U7TB9KnendKAIabzO82VpjFrwKrec06dyLYv%2Fl47NEnNZWsP5yaSd%2Fv9Nt9%2B3e3Fcj5wy9GquHyPxhZYGcXqjcR58XrA%2FHxLX5K0zXobvNO%2Fs9sQW8WXlQ8ZZ3I7tkqOCsmlz0iWex9%2B3URQDAvBsQdRLM8j%2F7%2FY5R8%3D&RelayState=https%3A%2F%2Fprofile.surfconext.nl%2F&SAMLEncoding=urn%3Aoasis%3Anames%3Atc%3ASAML%3A2.0%3Abindings%3AURL-Encoding%3Anone';
        $_SERVER['QUERY_STRING'] = $qs;

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
        $qs = 'SAMLRequest=nVLBauMwEP0Vo7sjW7FpKpJA2rBsoNuGOruHXhZFHm8EsuRqxtv27yvbWWgvYelFgjfvzbx5zBJVazu56enkHuG5B6TktbUO5VhYsT446RUalE61gJK0rDY%2F7qSYZbILnrz2ln2QXFYoRAhkvGPJbrtiv7VoygJEoTJ9LOusXDSFuJ4vdH6cxwoIEGUjsrqoFUt%2BQcCoXLHYKMoRe9g5JOUoQlleprlI8%2FyQz6W4ksXiiSXbuI1xikbViahDyfkRSM2wD40DmjnL0bSdhcE6Hx7BTd3xqnqoIPw1GmbdqWPJNx80jCGtGIUeWLL5t8mtd9i3EM78n493%2FzWr9XVvx%2B58mj39IlUaR%2FQmKOPq4Dtkyf4c9E1EjPtzOePjREL5%2FXDYp%2FuH6sDWy6G3HDML66%2B5ayO7VlHx2dySf2y9nM7pPprabffeGv02ZNcquux5QEydNiNVUlAODTiKMVvrX24DKIJz8nw9jfx8tOt3&RelayState=https%3A%2F%2Fbeta.surfnet.nl%2Fsimplesaml%2Fmodule.php%2Fcore%2Fauthenticate.php%3Fas%3DBraindrops&Signature=b%2Bqe%2FXGgICOrEL1v9dwuoy0RJtJ%2FGNAr7gJGYSJzLG0riPKwo7v5CH8GPC2P9IRikaeaNeQrnhBAaf8FCWrO0cLFw4qR6msK9bxRBGk%2BhIaTUYCh54ETrVCyGlmBneMgC5%2FiCRvtEW3ESPXCCqt8Ncu98yZmv9LIVyHSl67Se%2BfbB9sDw3%2FfzwYIHRMqK2aS8jnsnqlgnBGGOXqIqN3%2Bd%2F2dwtCfz14s%2F9odoYzSUv32qfNPiPez6PSNqwhwH7dWE3TlO%2FjZmz0DnOeQ2ft6qdZEi5ZN5KCV6VmNKpkrLMq6DDPnuwPm%2F8oCAoT88R2jG7uf9QZB%2BArWJKMEhDLsCA%3D%3D';
        $_SERVER['QUERY_STRING'] = $qs;

        $q = [
            'SAMLRequest' => 'nVLBauMwEP0Vo7sjW7FpKpJA2rBsoNuGOruHXhZFHm8EsuRqxtv27yvbWWgvYelFgjfvzbx5zBJVazu56enkHuG5B6TktbUO5VhYsT446RUalE61gJK0rDY/7qSYZbILnrz2ln2QXFYoRAhkvGPJbrtiv7VoygJEoTJ9LOusXDSFuJ4vdH6cxwoIEGUjsrqoFUt+QcCoXLHYKMoRe9g5JOUoQlleprlI8/yQz6W4ksXiiSXbuI1xikbViahDyfkRSM2wD40DmjnL0bSdhcE6Hx7BTd3xqnqoIPw1GmbdqWPJNx80jCGtGIUeWLL5t8mtd9i3EM78n493/zWr9XVvx+58mj39IlUaR/QmKOPq4Dtkyf4c9E1EjPtzOePjREL5/XDYp/uH6sDWy6G3HDML66+5ayO7VlHx2dySf2y9nM7pPprabffeGv02ZNcquux5QEydNiNVUlAODTiKMVvrX24DKIJz8nw9jfx8tOt3',
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
        $qs = 'SAMLRequest=cannotinflate';
        $_SERVER['QUERY_STRING'] = $qs;

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
        $qs = 'aap=noot&mies=jet&wim&RelayState=etc';
        $_SERVER['QUERY_STRING'] = $qs;

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
        $issuer = new Issuer('urn:x-simplesamlphp:issuer');

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
        $issuer = new Issuer('urn:x-simplesamlphp:issuer');

        $response = new Response($status, self::$clock->now(), $issuer);
        $hr = new HTTPRedirect();
        $hr->setDestination('gopher://myurl');
        $hr->send($response);
    }


    /**
     * Test that multiple query parameters with the same name are not allowed.
     *
     */
    public function testDuplicateQueryParameters(): void
    {
        /* @phpstan-ignore array.duplicateKey */
        $q = ['SAMLRequest' => 'noot', 'SAMLRequest' => 'jet&wim', 'RelayState' => 'etc'];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        // SAMLRequest appears twice
        $qs = 'SAMLRequest=first&RelayState=somestate&SAMLRequest=second&Signature=sig&&SigAlg=alg';
        $_SERVER['QUERY_STRING'] = $qs;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Duplicate parameter.');
        $hr = new HTTPRedirect();
        $hr->receive($request);
    }


    /**
     * Test that providing both SAMLRequest and SAMLResponse is not allowed
     */
    public function testSAMLResponseAndSAMLRequestConfusion(): void
    {
        $q = ['SAMLRequest' => 'noot', 'SAMLResponse' => 'jet&wim', 'RelayState' => 'etc'];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        // Both SAMLRequest and SAML Response appear
        $qs = 'SAMLRequest=first&RelayState=somestate&SAMLResponse=second&Signature=sig&&SigAlg=alg';
        $_SERVER['QUERY_STRING'] = $qs;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Both SAMLRequest and SAMLResponse provided.');
        $hr = new HTTPRedirect();
        $hr->receive($request);
    }
}
