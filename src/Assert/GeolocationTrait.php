<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * @package simplesamlphp/saml2
 */
trait GeolocationTrait
{
    // @TODO: this regex is incomplete, so reverting to the default URI-check
    //private static string $geolocation_regex = '/^geo:([-+]?\d+(?:\.\d+)?),([-+]?\d+(?:\.\d+)?)(?:\?z=(\d{1,2}))?$/';

    /**
     * @param string $value
     * @param string $message
     */
    protected static function validGeolocation(string $value, string $message = ''): void
    {
        // Assert::regex(
        //     $value,
        //     '/^geo:([-+]?\d+(?:\.\d+)?),([-+]?\d+(?:\.\d+)?)(?:\?z=(\d{1,2}))?$/',
        //     'Content is not a valid geolocation:  %s'
        // );
        // The regex above is incomplete, so for now we only test for a valid URI

        try {
            static::validAnyURI($value);
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException($e->getMessage());
        }
    }
}
