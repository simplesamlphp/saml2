<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Binding;

use Exception;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Binding\HTTPRedirect;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\XML\Type\IDValue;

/**
 * @package simplesamlphp\saml2
 */
#[Group('bindings')]
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
            'SAMLRequest' => 'pVJNbxMxEP0rK983+0E3aq0kUmiEiFQgagIHLsixx4kl73jxjKH8e7ybIkoPuXAazcd78+ZpFqR6P8h14jM+wvcExMVT75Hk1FiKFFEGRY4kqh5Ispb79YcH2c5qOcTAQQcvXkCuIxQRRHYBRbHdLMU3c3sHpmmttZ2pjzrHN/bW2rm6aerWzOu2uZkfdW3royi+QKSMXIpMlOFECbZIrJBzqW66smnL+u7QNrJrZdd9FcUmX+NQ8YQ6Mw8kqwrw5BBmlKLVAeGJZ+grle8HZKen4cqZoSKHJw8luROWo971H+n3ASn1EPcQfzgNnx8f/pJnR6zzr9nJ9YOH0Z2qDybl/nDOC8acLrEtlaapasCq5LmkQRS7Z3/fOjRZzXVrj5chku8Ph125+7Q/iNVi5JaTVXH1Pxp7YGUUq1cSF9XLBYvLL33M0rabXfBO/yrehdgrvq58rDhT2mlUJqQBtLMOTDbd+/DzPoJiWAqOCUS1uiz992dXvwE=',
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
            'SAMLResponse' => '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',
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
        $request = 'SAMLRequest=pVJNbxMxEP0rK983%2B0E3aq0kUmiEiFQgagIHLsixx4kl73jxjKH8e7ybIkoPuXAazcd78%2BZpFqR6P8h14jM%2BwvcExMVT75Hk1FiKFFEGRY4kqh5Ispb79YcH2c5qOcTAQQcvXkCuIxQRRHYBRbHdLMU3c3sHpmmttZ2pjzrHN%2FbW2rm6aerWzOu2uZkfdW3royi%2BQKSMXIpMlOFECbZIrJBzqW66smnL%2Bu7QNrJrZdd9FcUmX%2BNQ8YQ6Mw8kqwrw5BBmlKLVAeGJZ%2Bgrle8HZKen4cqZoSKHJw8luROWo971H%2Bn3ASn1EPcQfzgNnx8f%2FpJnR6zzr9nJ9YOH0Z2qDybl%2FnDOC8acLrEtlaapasCq5LmkQRS7Z3%2FfOjRZzXVrj5chku8Ph125%2B7Q%2FiNVi5JaTVXH1Pxp7YGUUq1cSF9XLBYvLL33M0rabXfBO%2Fyrehdgrvq58rDhT2mlUJqQBtLMOTDbd%2B%2FDzPoJiWAqOCUS1uiz992dXvwE%3D&RelayState=https%3A%2F%2Fprofile.surfconext.nl%2F&SAMLEncoding=urn%3Aoasis%3Anames%3Atc%3ASAML%3A2.0%3Abindings%3AURL-Encoding%3ADEFLATE';
        $_SERVER['QUERY_STRING'] = $request;

        $q = [
            'SAMLRequest' => 'pVJNbxMxEP0rK983+0E3aq0kUmiEiFQgagIHLsixx4kl73jxjKH8e7ybIkoPuXAazcd78+ZpFqR6P8h14jM+wvcExMVT75Hk1FiKFFEGRY4kqh5Ispb79YcH2c5qOcTAQQcvXkCuIxQRRHYBRbHdLMU3c3sHpmmttZ2pjzrHN/bW2rm6aerWzOu2uZkfdW3royi+QKSMXIpMlOFECbZIrJBzqW66smnL+u7QNrJrZdd9FcUmX+NQ8YQ6Mw8kqwrw5BBmlKLVAeGJZ+grle8HZKen4cqZoSKHJw8luROWo971H+n3ASn1EPcQfzgNnx8f/pJnR6zzr9nJ9YOH0Z2qDybl/nDOC8acLrEtlaapasCq5LmkQRS7Z3/fOjRZzXVrj5chku8Ph125+7Q/iNVi5JaTVXH1Pxp7YGUUq1cSF9XLBYvLL33M0rabXfBO/yrehdgrvq58rDhT2mlUJqQBtLMOTDbd+/DzPoJiWAqOCUS1uiz992dXvwE=',
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
            'SAMLRequest' => 'hVPLjtpAEPwVa+7GD+wljACJgKIgbbIISA65RI2nvYw0D2d6vNn8fcbGKFYe5GSpu6q7qqa9INCq4evWX8wBv7VIPnrVyhDvG0vWOsMtkCRuQCNxX/Hj+sMjzycpb5z1trKKjSj3GUCEzktrWLTbLtnX8iEDmGdpNoVyLqbzdDpNi3NZlOciq0QhYDYDgfOsqFn0GR0F5pKFQYFO1OLOkAfjQynNH+I0j/M3pzzlZcbT2RcWbYMbacD3rIv3DfEkkaKZaGtjcNVFvuDEqERb0SqcNJcm6Sx0kISkeVZ4lM/myRzRvcgKWbS+yd9YQ61GN3Q+HR5/LRCo7f820PWbx1BRXxVYQ6t8TA2L9kOsb6URQcX9RM9XEPH3p9M+3j8dT2y16GbzPiG3usn6TVFQoNGDAA+LZIxfXC/iY9i02+6tktWP6J11Gvx9IV1Firjuodw7MCTR+BCaUvb7xiF4XLIaFCFLbluGk0PRH2BI1eOrH/SPSxsVLueA9apTMHLCaxTo+hfmMMJzZSEffP11zrX3TwHJn7/F6ic=',
            'RelayState' => 'https://demo.moo-archive.nl/module.php/debugsp/test/default-sp',
            'SigAlg' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
            'Signature' => 'imAC2OqhtdL8BejmMvZG1+JgAaEo9JbMtaymXRJCkC0MYfKrda+Xfm3ZIJRi7SuCdw6wHLUsc0D2ZPI7DkLsqIQ/G8qZzdaPwLjSI+cEjKuGpLz+rTPofeRplqGhTfT32bQ4bwLEDEhBk6FxUDl63pTnYgo49Fi+3GlXtbmMK2I=',
        ];
        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $hr = new HTTPRedirect();
        $hr->setRelayState(urlencode($q['RelayState']));
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
            'SAMLRequest' => 'hVLLbttADPwVYe+ylJXsyAvbgBujqIG0MWK3h1wCVkvFC+xDXVJp+/ddywmS9uCeCJAznOGACwJne7Ue+Ojv8ceAxNkvZz2pcbAUQ/QqABlSHhyS4lbt159vlZyUqo+BQxuseEe5zAAijGyCF9l2sxSPjZ41jW5npZxjpWvsrisoZ9ddV8NU6is5nTczXTfTeS2ybxgpMZciLUp0ogG3nhg8p1Ypq7y8ymV1kJUqa1XJB5Ft0jXGA4+sI3NPqiiM7icuhBxiezTPOPG2cEEPFif9sS9OJ5wgBRn/ZHFvnvyd32N8Ni2KbP1q/yZ4GhzGl8nX+9s3AY0u/E+BzlXm0NLY1djBYDmnXmS7l1g/GK+Ti8uJfj+DSH06HHb57m5/EKvFabcaE4qrV1v/OEoOHDJoYFgU7/GL80d8SUrbzS5Y0/7OPobogC8bOXWMzrsRqjiCJ4OeU2jWhp83EYFxKTgOKIrVWfLvv1v9AQ==',
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
            'SAMLRequest' => 'hVPLjtpAEPwVa+7GD+wljACJgKIgbbIISA65RI2nvYw0D2d6vNn8fcbGKFYe5GSpu6q7qqa9INCq4evWX8wBv7VIPnrVyhDvG0vWOsMtkCRuQCNxX/Hj+sMjzycpb5z1trKKjSj3GUCEzktrWLTbLtnX8iEDmGdpNoVyLqbzdDpNi3NZlOciq0QhYDYDgfOsqFn0GR0F5pKFQYFO1OLOkAfjQynNH+I0j/M3pzzlZcbT2RcWbYMbacD3rIv3DfEkkaKZaGtjcNVFvuDEqERb0SqcNJcm6Sx0kISkeVZ4lM/myRzRvcgKWbS+yd9YQ61GN3Q+HR5/LRCo7f820PWbx1BRXxVYQ6t8TA2L9kOsb6URQcX9RM9XEPH3p9M+3j8dT2y16GbzPiG3usn6TVFQoNGDAA+LZIxfXC/iY9i02+6tktWP6J11Gvx9IV1Firjuodw7MCTR+BCaUvb7xiF4XLIaFCFLbluGk0PRH2BI1eOrH/SPSxsVLueA9apTMHLCaxTo+hfmMMJzZSEffP11zrX3TwHJn7/F6ic=',
            'RelayState' => 'https://demo.moo-archive.nl/module.php/debugsp/test/default-sp',
            'Signature' => 'imAC2OqhtdL8BejmMvZG1+JgAaEo9JbMtaymXRJCkC0MYfKrda+Xfm3ZIJRi7SuCdw6wHLUsc0D2ZPI7DkLsqIQ/G8qZzdaPwLjSI+cEjKuGpLz+rTPofeRplqGhTfT32bQ4bwLEDEhBk6FxUDl63pTnYgo49Fi+3GlXtbmMK2I=',
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
        $request = new AuthnRequest(
            id: IDValue::fromString('abc123'),
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );

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
        $request = new AuthnRequest(
            id: IDValue::fromString('abc123'),
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );
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
        $status = new Status(
            new StatusCode(
                SAMLAnyURIValue::fromString(C::STATUS_SUCCESS),
            ),
        );
        $issuer = new Issuer(
            SAMLStringValue::fromString('urn:x-simplesamlphp:issuer'),
        );

        $response = new Response(
            id: IDValue::fromString('abc123'),
            status: $status,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            issuer: $issuer,
            destination: SAMLAnyURIValue::fromString('http://example.org/login?success=yes'),
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
        $status = new Status(
            new StatusCode(
                SAMLAnyURIValue::fromString(C::STATUS_SUCCESS),
            ),
        );
        $issuer = new Issuer(
            SAMLStringValue::fromString('urn:x-simplesamlphp:issuer'),
        );

        $response = new Response(
            id: IDValue::fromString('abc123'),
            status: $status,
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
            issuer: $issuer,
        );

        $hr = new HTTPRedirect();
        $hr->setDestination('gopher://myurl');
        $hr->send($response);
    }


    /**
     * Test that multiple query parameters with the same name are properly dealt with.
     * We only consistently use the last value provided - CVE-2025-27773
     */
    public function testDuplicateQueryParameters(): void
    {
        $q = [
            /** @phpstan-ignore array.duplicateKey */
            'SAMLRequest' => 'pVJNbxMxEP0rK983+0E3aq0kUmiEiFQgagIHLsixx4kl73jxjKH8e7ybIkoPuXAazcd78+ZpFqR6P8h14jM+wvcExMVT75Hk1FiKFFEGRY4kqh5Ispb79YcH2c5qOcTAQQcvXkCuIxQRRHYBRbHdLMU3c3sHpmmttZ2pjzrHN/bW2rm6aerWzOu2uZkfdW3royi+QKSMXIpMlOFECbZIrJBzqW66smnL+u7QNrJrZdd9FcUmX+NQ8YQ6Mw8kqwrw5BBmlKLVAeGJZ+grle8HZKen4cqZoSKHJw8luROWo971H+n3ASn1EPcQfzgNnx8f/pJnR6zzr9nJ9YOH0Z2qDybl/nDOC8acLrEtlaapasCq5LmkQRS7Z3/fOjRZzXVrj5chku8Ph125+7Q/iNVi5JaTVXH1Pxp7YGUUq1cSF9XLBYvLL33M0rabXfBO/yrehdgrvq58rDhT2mlUJqQBtLMOTDbd+/DzPoJiWAqOCUS1uiz992dXvwE=',
            'SAMLRequest' => 'hVPLjtpAEPwVa+7GD+wljACJgKIgbbIISA65RI2nvYw0D2d6vNn8fcbGKFYe5GSpu6q7qqa9INCq4evWX8wBv7VIPnrVyhDvG0vWOsMtkCRuQCNxX/Hj+sMjzycpb5z1trKKjSj3GUCEzktrWLTbLtnX8iEDmGdpNoVyLqbzdDpNi3NZlOciq0QhYDYDgfOsqFn0GR0F5pKFQYFO1OLOkAfjQynNH+I0j/M3pzzlZcbT2RcWbYMbacD3rIv3DfEkkaKZaGtjcNVFvuDEqERb0SqcNJcm6Sx0kISkeVZ4lM/myRzRvcgKWbS+yd9YQ61GN3Q+HR5/LRCo7f820PWbx1BRXxVYQ6t8TA2L9kOsb6URQcX9RM9XEPH3p9M+3j8dT2y16GbzPiG3usn6TVFQoNGDAA+LZIxfXC/iY9i02+6tktWP6J11Gvx9IV1Firjuodw7MCTR+BCaUvb7xiF4XLIaFCFLbluGk0PRH2BI1eOrH/SPSxsVLueA9apTMHLCaxTo+hfmMMJzZSEffP11zrX3TwHJn7/F6ic=',
            'RelayState' => 'https://demo.moo-archive.nl/module.php/debugsp/test/default-sp',
            'SigAlg' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
            'Signature' => 'imAC2OqhtdL8BejmMvZG1+JgAaEo9JbMtaymXRJCkC0MYfKrda+Xfm3ZIJRi7SuCdw6wHLUsc0D2ZPI7DkLsqIQ/G8qZzdaPwLjSI+cEjKuGpLz+rTPofeRplqGhTfT32bQ4bwLEDEhBk6FxUDl63pTnYgo49Fi+3GlXtbmMK2I=',
        ];

        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $hr = new HTTPRedirect();
        $hr->receive($request);

        $q = [
            /** @phpstan-ignore array.duplicateKey */
            'SAMLRequest' => 'pVJNbxMxEP0rK983+0E3aq0kUmiEiFQgagIHLsixx4kl73jxjKH8e7ybIkoPuXAazcd78+ZpFqR6P8h14jM+wvcExMVT75Hk1FiKFFEGRY4kqh5Ispb79YcH2c5qOcTAQQcvXkCuIxQRRHYBRbHdLMU3c3sHpmmttZ2pjzrHN/bW2rm6aerWzOu2uZkfdW3royi+QKSMXIpMlOFECbZIrJBzqW66smnL+u7QNrJrZdd9FcUmX+NQ8YQ6Mw8kqwrw5BBmlKLVAeGJZ+grle8HZKen4cqZoSKHJw8luROWo971H+n3ASn1EPcQfzgNnx8f/pJnR6zzr9nJ9YOH0Z2qDybl/nDOC8acLrEtlaapasCq5LmkQRS7Z3/fOjRZzXVrj5chku8Ph125+7Q/iNVi5JaTVXH1Pxp7YGUUq1cSF9XLBYvLL33M0rabXfBO/yrehdgrvq58rDhT2mlUJqQBtLMOTDbd+/DzPoJiWAqOCUS1uiz992dXvwE=',
            'SAMLRequest' => 'hVPLjtpAEPwVa+7GD+wljACJgKIgbbIISA65RI2nvYw0D2d6vNn8fcbGKFYe5GSpu6q7qqa9INCq4evWX8wBv7VIPnrVyhDvG0vWOsMtkCRuQCNxX/Hj+sMjzycpb5z1trKKjSj3GUCEzktrWLTbLtnX8iEDmGdpNoVyLqbzdDpNi3NZlOciq0QhYDYDgfOsqFn0GR0F5pKFQYFO1OLOkAfjQynNH+I0j/M3pzzlZcbT2RcWbYMbacD3rIv3DfEkkaKZaGtjcNVFvuDEqERb0SqcNJcm6Sx0kISkeVZ4lM/myRzRvcgKWbS+yd9YQ61GN3Q+HR5/LRCo7f820PWbx1BRXxVYQ6t8TA2L9kOsb6URQcX9RM9XEPH3p9M+3j8dT2y16GbzPiG3usn6TVFQoNGDAA+LZIxfXC/iY9i02+6tktWP6J11Gvx9IV1Firjuodw7MCTR+BCaUvb7xiF4XLIaFCFLbluGk0PRH2BI1eOrH/SPSxsVLueA9apTMHLCaxTo+hfmMMJzZSEffP11zrX3TwHJn7/F6ic=',
            'RelayState' => 'https://demo.moo-archive.nl/module.php/admin/test/default-sp',
            'SigAlg' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
            'Signature' => 'T8+SG10HcgOkpw4cUFTnoF9WWrlYnllnqKruvmVcyinbiJsdnw7EMxM6Lr/5Mo/Rk3Hd7x8tuQ955Vv96jMRKGfdvq8Dh1gx4PKJPHXFWBSipWOc9UDNT0N3addnk9PiSaQ5YehT9lZ4agoSmKqiWNrE4qpKIcgWdh0GgiYDUto=',
        ];

        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to verify signature.');
        $hr = new HTTPRedirect();
        $hr->receive($request);
    }


    /**
     * Test that providing both SAMLRequest and SAMLResponse is not allowed - CVE-2025-27773
     */
    public function testSAMLResponseAndSAMLRequestConfusion(): void
    {
        $q = ['SAMLRequest' => 'noot', 'SAMLResponse' => 'jet&wim', 'RelayState' => 'etc'];

        $request = new ServerRequest('GET', 'http://tnyholm.se');
        $request = $request->withQueryParams($q);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Both SAMLRequest and SAMLResponse provided.');

        $hr = new HTTPRedirect();
        $hr->receive($request);
    }
}
