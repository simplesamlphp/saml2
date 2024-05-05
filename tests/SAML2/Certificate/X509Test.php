<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Certificate;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Certificate\X509;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(X509::class)]
final class X509Test extends TestCase
{
    /**
     */
    #[Group('certificate')]
    public function testX509CertificateContentsMustBeStrippedOfWhitespace(): void
    {
        $toTest = [
            'X509Certificate' => ' Should   No Longer  Have Whitespaces',
        ];

        $viaConstructor = new X509($toTest);
        $viaSetting = new X509([]);
        $viaSetting['X509Certificate'] = $toTest['X509Certificate'];
        $viaFactory = X509::createFromCertificateData($toTest['X509Certificate']);

        $this->assertEquals($viaConstructor['X509Certificate'], 'ShouldNoLongerHaveWhitespaces');
        $this->assertEquals($viaSetting['X509Certificate'], 'ShouldNoLongerHaveWhitespaces');
        $this->assertEquals($viaFactory['X509Certificate'], 'ShouldNoLongerHaveWhitespaces');
    }
}
