<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;

/**
 * Helper functions for the SAML2 library.
 *
 * @package simplesamlphp/saml2
 */
class Utils
{
    /**
     * Create a KeyDescriptor with the given certificate.
     *
     * @param string $x509Data The certificate, as a base64-encoded PEM data.
     * @return \SimpleSAML\SAML2\XML\md\KeyDescriptor The keydescriptor.
     */
    public static function createKeyDescriptor(string $x509Data): KeyDescriptor
    {
        $x509Data = new X509Data([
            new X509Certificate($x509Data)
        ]);

        $keyInfo = new KeyInfo([
            $x509Data
        ]);

        return new KeyDescriptor($keyInfo);
    }


    /**
     * @return \SimpleSAML\SAML2\Compat\AbstractContainer
     */
    public static function getContainer(): AbstractContainer
    {
        return ContainerSingleton::getInstance();
    }
}
