<?php

declare(strict_types=1);

namespace SAML2\Certificate;

class X509Test extends \PHPUnit\Framework\TestCase
{
    /**
     * @group certificate
     *
     * @test
     */
    public function x509_certificate_contents_must_be_stripped_of_whitespace()
    {
        $toTest = [
            'X509Certificate' => ' Should   No Longer  Have Whitespaces'
        ];

        $viaConstructor                = new X509($toTest);
        $viaSetting                    = new X509([]);
        $viaSetting['X509Certificate'] = $toTest['X509Certificate'];
        $viaFactory                    = X509::createFromCertificateData($toTest['X509Certificate']);

        $this->assertEquals($viaConstructor['X509Certificate'], 'ShouldNoLongerHaveWhitespaces');
        $this->assertEquals($viaSetting['X509Certificate'], 'ShouldNoLongerHaveWhitespaces');
        $this->assertEquals($viaFactory['X509Certificate'], 'ShouldNoLongerHaveWhitespaces');
    }
}
