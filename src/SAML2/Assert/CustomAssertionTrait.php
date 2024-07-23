<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\Assert\Assert as BaseAssert;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\SchemaViolationException;

use function sprintf;

/**
 * @package simplesamlphp/assert
 */
trait CustomAssertionTrait
{
    private const SCHEME_REGEX = '/^([a-z][a-z0-9\+\-\.]+[:])/i';

    /***********************************************************************************
     *  NOTE:  Custom assertions may be added below this line.                         *
     *         They SHOULD be marked as `private` to ensure the call is forced         *
     *          through __callStatic().                                                *
     *         Assertions marked `public` are called directly and will                 *
     *          not handle any custom exception passed to it.                          *
     ***********************************************************************************/


    /**
     * @param string $value
     * @param string $message
     */
    private static function validDateTime(string $value, string $message = ''): void
    {
        try {
            BaseAssert::validDateTime($value, '\'%s\' is not a valid xs:dateTime');
        } catch (AssertionFailedException $e) {
            throw new SchemaValidationException(sprintf(
                $e->getMessage(),
                $value,
            ));
        }

        try {
            BaseAssert::endsWith(
                $value,
                'Z',
                '\'%s\' is not a DateTime expressed in the UTC timezone using the \'Z\' timezone identifier.',
            );
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException(sprintf(
                $e->getMessage(),
                $value,
            ));
        }
    }


    /**
     * @param string $value
     * @param string $message
     */
    private static function validURI(string $value, string $message = ''): void
    {
        try {
            BaseAssert::validURI($value, '\'%s\' is not a valid RFC3986 compliant URI');
        } catch (AssertionFailedException $e) {
            throw new SchemaViolationException(sprintf(
                $e->getMessage(),
                $value,
            ));
        }

        try {
            BaseAssert::notWhitespaceOnly($value, $message ?: '\'%s\' is not a SAML2-compliant URI');

            // If it doesn't have a scheme, it's not an absolute URI
            BaseAssert::regex($value, self::SCHEME_REGEX, $message ?: '\'%s\' is not a SAML2-compliant URI');
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException(sprintf(
                $e->getMessage(),
                $value,
            ));
        }
    }
}
