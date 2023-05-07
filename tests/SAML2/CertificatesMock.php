<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class \SAML2\CertificatesMock
 */
class CertificatesMock
{
    public const PUBLIC_KEY_PEM = '-----BEGIN CERTIFICATE-----
MIICgTCCAeoCCQCbOlrWDdX7FTANBgkqhkiG9w0BAQUFADCBhDELMAkGA1UEBhMC
Tk8xGDAWBgNVBAgTD0FuZHJlYXMgU29sYmVyZzEMMAoGA1UEBxMDRm9vMRAwDgYD
VQQKEwdVTklORVRUMRgwFgYDVQQDEw9mZWlkZS5lcmxhbmcubm8xITAfBgkqhkiG
9w0BCQEWEmFuZHJlYXNAdW5pbmV0dC5ubzAeFw0wNzA2MTUxMjAxMzVaFw0wNzA4
MTQxMjAxMzVaMIGEMQswCQYDVQQGEwJOTzEYMBYGA1UECBMPQW5kcmVhcyBTb2xi
ZXJnMQwwCgYDVQQHEwNGb28xEDAOBgNVBAoTB1VOSU5FVFQxGDAWBgNVBAMTD2Zl
aWRlLmVybGFuZy5ubzEhMB8GCSqGSIb3DQEJARYSYW5kcmVhc0B1bmluZXR0Lm5v
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDivbhR7P516x/S3BqKxupQe0LO
NoliupiBOesCO3SHbDrl3+q9IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHIS
KOtPlAeTZSnb8QAu7aRjZq3+PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d
1EDwXJW1rRXuUt4C8QIDAQABMA0GCSqGSIb3DQEBBQUAA4GBACDVfp86HObqY+e8
BUoWQ9+VMQx1ASDohBjwOsg2WykUqRXF+dLfcUH9dWR63CtZIKFDbStNomPnQz7n
bK+onygwBspVEbnHuUihZq3ZUdmumQqCw4Uvs/1Uvq3orOo/WJVhTyvLgFVK2Qar
Q4/67OZfHd7R+POBXhophSMv1ZOo
-----END CERTIFICATE-----';

    public const PUBLIC_KEY_PEM_CONTENTS = 'MIICgTCCAeoCCQCbOlrWDdX7FTANBgkqhkiG9w0BAQUFADCBhDELMAkGA1UEBhMCTk8xGDAWBgNVBAgTD0FuZHJlYXMgU29sYmVyZzEMMAoGA1UEBxMDRm9vMRAwDgYDVQQKEwdVTklORVRUMRgwFgYDVQQDEw9mZWlkZS5lcmxhbmcubm8xITAfBgkqhkiG9w0BCQEWEmFuZHJlYXNAdW5pbmV0dC5ubzAeFw0wNzA2MTUxMjAxMzVaFw0wNzA4MTQxMjAxMzVaMIGEMQswCQYDVQQGEwJOTzEYMBYGA1UECBMPQW5kcmVhcyBTb2xiZXJnMQwwCgYDVQQHEwNGb28xEDAOBgNVBAoTB1VOSU5FVFQxGDAWBgNVBAMTD2ZlaWRlLmVybGFuZy5ubzEhMB8GCSqGSIb3DQEJARYSYW5kcmVhc0B1bmluZXR0Lm5vMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDivbhR7P516x/S3BqKxupQe0LONoliupiBOesCO3SHbDrl3+q9IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHISKOtPlAeTZSnb8QAu7aRjZq3+PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d1EDwXJW1rRXuUt4C8QIDAQABMA0GCSqGSIb3DQEBBQUAA4GBACDVfp86HObqY+e8BUoWQ9+VMQx1ASDohBjwOsg2WykUqRXF+dLfcUH9dWR63CtZIKFDbStNomPnQz7nbK+onygwBspVEbnHuUihZq3ZUdmumQqCw4Uvs/1Uvq3orOo/WJVhTyvLgFVK2QarQ4/67OZfHd7R+POBXhophSMv1ZOo';

    public const PRIVATE_KEY_PEM = '-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQDivbhR7P516x/S3BqKxupQe0LONoliupiBOesCO3SHbDrl3+q9
IbfnfmE04rNuMcPsIxB161TdDpIesLCn7c8aPHISKOtPlAeTZSnb8QAu7aRjZq3+
PbrP5uW3TcfCGPtKTytHOge/OlJbo078dVhXQ14d1EDwXJW1rRXuUt4C8QIDAQAB
AoGAD4/Z4LWVWV6D1qMIp1Gzr0ZmdWTE1SPdZ7Ej8glGnCzPdguCPuzbhGXmIg0V
J5D+02wsqws1zd48JSMXXM8zkYZVwQYIPUsNn5FetQpwxDIMPmhHg+QNBgwOnk8J
K2sIjjLPL7qY7Itv7LT7Gvm5qSOkZ33RCgXcgz+okEIQMYkCQQDzbTOyDL0c5WQV
6A2k06T/azdhUdGXF9C0+WkWSfNaovmTgRXh1G+jMlr82Snz4p4/STt7P/XtyWzF
3pkVgZr3AkEA7nPjXwHlttNEMo6AtxHd47nizK2NUN803ElIUT8P9KSCoERmSXq6
6PDekGNic4ldpsSvOeYCk8MAYoDBy9kvVwJBAMLgX4xg6lzhv7hR5+pWjTb1rIY6
rCHbrPfU264+UZXz9v2BT/VUznLF81WMvStD9xAPHpFS6R0OLghSZhdzhI0CQQDL
8Duvfxzrn4b9QlmduV8wLERoT6rEVxKLsPVz316TGrxJvBZLk/cV0SRZE1cZf4uk
XSWMfEcJ/0Zt+LdG1CqjAkEAqwLSglJ9Dy3HpgMz4vAAyZWzAxvyA1zW0no9GOLc
PQnYaNUN/Fy2SYtETXTb0CQ9X1rt8ffkFP7ya+5TC83aMg==
-----END RSA PRIVATE KEY-----';

    public const PUBLIC_KEY_2_PEM = '-----BEGIN CERTIFICATE-----
MIIEdjCCA16gAwIBAgIRALcDQnHscLnQAL4GULGaHsIwDQYJKoZIhvcNAQEFBQAw
NjELMAkGA1UEBhMCTkwxDzANBgNVBAoTBlRFUkVOQTEWMBQGA1UEAxMNVEVSRU5B
IFNTTCBDQTAeFw0wOTEwMjYwMDAwMDBaFw0xMjEwMjUyMzU5NTlaMFExCzAJBgNV
BAYTAk5MMRUwEwYDVQQKEwxTVVJGbmV0IEIuVi4xETAPBgNVBAsTCFNlcnZpY2Vz
MRgwFgYDVQQDEw9iZXRhLnN1cmZuZXQubmwwggEiMA0GCSqGSIb3DQEBAQUAA4IB
DwAwggEKAoIBAQC7D6V4dl41AF70veMMQqL7kpk+p06qU9EW2YUuJAnwhRO1Sfbi
qPweitLE7ReXfK/vSPvdFG194gZjJ4JS7M7pP00eolfEK6ljLhMoUrzbBuEc5LoQ
B6HsOogod7YrJiVRXLzM4bV2LzbyjAwBgForoM4l576CjbN/NDAPO3YvbktodR5D
H/nPiw/5L7w2KS3x55xUd8clY7Nji9W7XbviMUAugSkkC2ethl0AMpdmk6BI5pjy
r6KCUrGz9bKl101yUGhrkHy1NpHagOoxakkgVDgi2Erf8aDnkCe8A419m2mBD1WA
9uQWF5eMpiY8fDMwjwSFDFoSUlJPhiKN8vpnAgMBAAGjggFiMIIBXjAfBgNVHSME
GDAWgBQMvZNoDPPeq6NJays3V0fqkOO57TAdBgNVHQ4EFgQUrCpeMIzTW5M13G4i
b1B+yq7Fm60wDgYDVR0PAQH/BAQDAgWgMAwGA1UdEwEB/wQCMAAwHQYDVR0lBBYw
FAYIKwYBBQUHAwEGCCsGAQUFBwMCMBgGA1UdIAQRMA8wDQYLKwYBBAGyMQECAh0w
OgYDVR0fBDMwMTAvoC2gK4YpaHR0cDovL2NybC50Y3MudGVyZW5hLm9yZy9URVJF
TkFTU0xDQS5jcmwwbQYIKwYBBQUHAQEEYTBfMDUGCCsGAQUFBzAChilodHRwOi8v
Y3J0LnRjcy50ZXJlbmEub3JnL1RFUkVOQVNTTENBLmNydDAmBggrBgEFBQcwAYYa
aHR0cDovL29jc3AudGNzLnRlcmVuYS5vcmcwGgYDVR0RBBMwEYIPYmV0YS5zdXJm
bmV0Lm5sMA0GCSqGSIb3DQEBBQUAA4IBAQC8iCICDbP4/8JTDeLPfo/n6roOvpMs
teQt7X5oN2Ka1xgKflpBGqJO5o3PcnfP437kcLRTnp6XDyTfS4eyZdxCqCECR2Pb
8nbULVFv9hyF6asIWUfbJ67CFcRIpcuaD5habSrg8+rT86DjKdtYQKwbKL+rNbOs
g6/ROR7vJgbSqrBLraXvl8HDUq5+lSF/II4LHVzNM8TpQlMY4ynRP6GEjcNUTH3I
FKPQk+NwBYQqJ83Uil/36kbXsHQ81o/Vp6it7tlvLBOP1EN9jNGUXZuAqvFphNkw
EJpABx1x4ukY8bZVl6QzQ79P48oGxOaIy27/g1FVkGqRtA4UPABcn0sJ
-----END CERTIFICATE-----';

    public const PUBLIC_KEY_DSA_PEM = '-----BEGIN CERTIFICATE-----
MIIDXTCCAxqgAwIBAgIJAO/P24rWSVJKMAsGCWCGSAFlAwQDAjBmMQswCQYDVQQG
EwJOTDEQMA4GA1UECAwHVXRyZWNodDEQMA4GA1UEBwwHVXRyZWNodDEQMA4GA1UE
CgwHU1VSRm5ldDEhMB8GA1UEAwwYQ2VydGlmaWNhdGUgd2l0aCBEU0Ega2V5MB4X
DTE2MTEyOTE1MzU0MloXDTE2MTIyOTE1MzU0MlowZjELMAkGA1UEBhMCTkwxEDAO
BgNVBAgMB1V0cmVjaHQxEDAOBgNVBAcMB1V0cmVjaHQxEDAOBgNVBAoMB1NVUkZu
ZXQxITAfBgNVBAMMGENlcnRpZmljYXRlIHdpdGggRFNBIGtleTCCAbcwggEsBgcq
hkjOOAQBMIIBHwKBgQDymea94rRzJ9Xtj7EoaXuYH8X9a2E0Ei8wfx+9lZK5C8Fm
5wgTYeTGXV45Tf4VZ+eqz6sU4XQC6ehVIlxdO9PvodYgQdB3aGlDW9mhcVM/kL9v
AIRgLMHMwyph6FDWD/uKyw6hH4A7XKer09SIfmqwhUqg27Xm5pKVH3kYOUGsBwIV
ANooxK2eY8ojkNRjxebok0tbKD/tAoGBAMQawu3dHEDtKzYuGrSD9NxGLRB5NI0k
h4qvliwD6ur2IDrrnxmN/VY0QqwOT6AWChiIur5glBP7zlG2GBR03FrMaJRF727r
ExSzWETQKKgXx9vQpw6jcwIiHoQhullzjLr8qFQsOsNRnXeKmSvZxEJKRKhAUSAu
0yEnLkJc4F44A4GEAAKBgF6rEBWslH8aV/iM07JjC+kcLPcG5Yp619KLcSfWt030
CU2A8azmtNeQZ1FB/sg2PjciQ8qgcxFXBRHkUS/173WXb+6dDTuFBxwTYBVJM+ZD
Zmm5GEXjGbZN2tV0s1ULp+plbOwROLC8F5oyZE2fvTAvqZ9XHeWIZkgyoVwSuvXO
o1AwTjAdBgNVHQ4EFgQUC12Td80rgZbLXfvMefDul5w/S/YwHwYDVR0jBBgwFoAU
C12Td80rgZbLXfvMefDul5w/S/YwDAYDVR0TBAUwAwEB/zALBglghkgBZQMEAwID
MAAwLQIUKvKKf7u2pLv5JAsc5E5QOpZ9JWoCFQCVymKmF6aYAOJxuSlUj+vF1n6p
UQ==
-----END CERTIFICATE-----';


    /**
     * @return XMLSecurityKey
     */
    public static function getPublicKey(): XMLSecurityKey
    {
        $publicKey = new XMLSecurityKey(XMLSecurityKey::RSA_1_5, ['type' => 'public']);
        $publicKey->loadKey(self::PUBLIC_KEY_PEM);
        return $publicKey;
    }


    /**
     * @return XMLSecurityKey
     */
    public static function getPrivateKey(): XMLSecurityKey
    {
        $privateKey = new XMLSecurityKey(XMLSecurityKey::RSA_1_5, ['type' => 'private']);
        $privateKey->loadKey(self::PRIVATE_KEY_PEM);
        return $privateKey;
    }


    /**
     * @return XMLSecurityKey
     */
    public static function getPublicKey2(): XMLSecurityKey
    {
        $publicKey = new XMLSecurityKey(XMLSecurityKey::RSA_1_5, ['type' => 'public']);
        $publicKey->loadKey(self::PUBLIC_KEY_2_PEM);
        return $publicKey;
    }


    /**
     * @return XMLSecurityKey
     */
    public static function getPublicKey3(): XMLSecurityKey
    {
        $publicKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'public']);
        $publicKey->loadKey(self::PUBLIC_KEY_3_PEM);
        return $publicKey;
    }


    /**
     * @return XMLSecurityKey
     */
    public static function getPublicKeySha256(): XMLSecurityKey
    {
        $publicKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $publicKey->loadKey(self::PUBLIC_KEY_PEM);
        return $publicKey;
    }


    /**
     * @return XMLSecurityKey
     */
    public static function getPublicKey2Sha256(): XMLSecurityKey
    {
        $publicKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $publicKey->loadKey(self::PUBLIC_KEY_2_PEM);
        return $publicKey;
    }


    /**
     * Load a X.509 certificate with a DSA public key as RSA key
     * @return XMLSecurityKey
     */
    public static function getPublicKeyDSAasRSA(): XMLSecurityKey
    {
        $publicKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $publicKey->loadKey(self::PUBLIC_KEY_DSA_PEM);
        return $publicKey;
    }


    /**
     * @return string
     */
    public static function getPlainPublicKey(): string
    {
        return self::PUBLIC_KEY_PEM;
    }


    /**
     * @return string
     */
    public static function getPlainPrivateKey(): string
    {
        return self::PRIVATE_KEY_PEM;
    }


    /**
     * Returns just the certificate contents without the begin and end markings
     * @return string
     */
    public static function getPlainPublicKeyContents(): string
    {
        return self::PUBLIC_KEY_PEM_CONTENTS;
    }


    /**
     * Returns malformed public key by truncating it.
     * @return string
     */
    public static function getPlainInvalidPublicKey(): string
    {
        return substr(self::PUBLIC_KEY_PEM, 200);
    }
}
