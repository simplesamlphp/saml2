<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\XMLSecurity\Assert\Assert as BaseAssert;

/**
 * SimpleSAML\SAML2\Assert\Assert wrapper class
 *
 * @package simplesamlphp/saml2
 *
 * @method static void validDateTime(mixed $value, string $message = '', string $exception = '')
 * @method static void validEntityID(mixed $value, string $message = '', string $exception = '')
 * @method static void validURI(mixed $value, string $message = '', string $exception = '')
 * @method static void validRelayState(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidDateTime(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidEntityID(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidRelayState(mixed $value, string $message = '', string $exception = '')
 * @method static void nullOrValidURI(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidDateTime(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidEntityID(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidRelayState(mixed $value, string $message = '', string $exception = '')
 * @method static void allValidURI(mixed $value, string $message = '', string $exception = '')
 */
class Assert extends BaseAssert
{
    use CustomAssertionTrait;
    use RelayStateTrait;
}
