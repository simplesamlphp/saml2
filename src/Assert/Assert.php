<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\XMLSecurity\Assert\Assert as BaseAssert;

/**
 * SimpleSAML\SAML2\Assert\Assert wrapper class
 *
 * @package simplesamlphp/saml2
 *
 * @method static void validCIDR(mixed $value, string $message = '', string $exception = '')
 * @method static void validDomain(mixed $value, string $message = '', string $exception = '')
 * @method static void validEntityID(mixed $value, string $message = '', string $exception = '')
 * @method static void validGeolocation(mixed $value, string $message = '', string $exception = '')
 * @method static void validRelayState(mixed $value, string $message = '', string $exception = '')
 * @method static void validSAMLAnyURI(mixed $value, string $message = '', string $exception = '')
 * @method static void validSAMLDateTime(mixed $value, string $message = '', string $exception = '')
 * @method static void validSAMLString(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidCIDR(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidDomain(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidEntityID(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidGeolocation(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidRelayState(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidSAMLAnyURI(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidSAMLDateTime(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidSAMLString(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidCIDR(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidDomain(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidEntityID(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidGeolocation(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidRelayState(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidSAMLAnyURI(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidSAMLDateTime(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidSAMLString(mixed $value, string $message = '', string $exception = '')
 */
class Assert extends BaseAssert
{
    use CIDRTrait;
    use DomainTrait;
    use EntityIDTrait;
    use GeolocationTrait;
    use RelayStateTrait;
    use SAMLAnyURITrait;
    use SAMLDateTimeTrait;
    use SAMLStringTrait;
}
