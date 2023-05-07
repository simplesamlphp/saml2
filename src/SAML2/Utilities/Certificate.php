<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Utilities;

use function chunk_split;
use function preg_match;

/**
 * Collection of Utility functions specifically for certificates
 */
class Certificate
{
    /**
     * The pattern that the contents of a certificate should adhere to
     */
    public const CERTIFICATE_PATTERN = '/^-----BEGIN CERTIFICATE-----([^-]*)^-----END CERTIFICATE-----/m';

    /**
     * @param string $certificate
     *
     * @return bool
     */
    public static function hasValidStructure(string $certificate): bool
    {
        return !!preg_match(self::CERTIFICATE_PATTERN, $certificate);
    }


    /**
     * @param string $X509CertificateContents
     *
     * @return string
     */
    public static function convertToCertificate(string $X509CertificateContents): string
    {
        return "-----BEGIN CERTIFICATE-----\n"
                . chunk_split($X509CertificateContents, 64)
                . "-----END CERTIFICATE-----\n";
    }
}
