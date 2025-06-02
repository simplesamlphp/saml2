<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * @package simplesamlphp/saml2
 */
trait CIDRTrait
{
    private static string $cidr_regex = '/^
      (?:
        (?:
          (
            (?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.
            (?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.
            (?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.
            (?:25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])
          )
          [\/](3[0-2]|[1-2]?[0-9])$
        )
        |
        (
          (?:[0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|
          (?:[0-9a-fA-F]{1,4}:){1,7}:|
          (?:[0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|
          (?:[0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|
          (?:[0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|
          (?:[0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|
          (?:[0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|
          [0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|
          :(?:(:[0-9a-fA-F]{1,4}){1,7}|:)|
          ::
        )
        [\/](12[0-8]|1[0-1][0-9]|[1-9]?[0-9])$
      )
      $/Dxi';


    /**
     * @param string $value
     * @param string $message
     */
    protected static function validCIDR(string $value, string $message = ''): void
    {
        try {
            parent::regex(
                $value,
                self::$cidr_regex,
                $message ?: '%s is not a valid RFC4632 CIDR-block',
            );
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException($e->getMessage());
        }
    }
}
