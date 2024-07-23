<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\Assert\Assert as BaseAssert;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\SchemaViolationException;

/**
 * @package simplesamlphp/assert
 */
trait CustomAssertionTrait
{
    private static string $scheme_regex = '/^([a-z][a-z0-9\+\-\.]+[:])/i';

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
            BaseAssert::validDateTime($value);
        } catch (AssertionFailedException $e) {
            throw new SchemaViolationException($e->getMessage());
        }

        try {
            BaseAssert::endsWith(
                $value,
                'Z',
                '%s is not a DateTime expressed in the UTC timezone using the \'Z\' timezone identifier.',
            );
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException($e->getMessage());
        }
    }


    /**
     * @param string $value
     * @param string $message
     */
    private static function validURI(string $value, string $message = ''): void
    {
        try {
            BaseAssert::validURI($value);
        } catch (AssertionFailedException $e) {
            throw new SchemaViolationException($e->getMessage());
        }

        try {
            BaseAssert::notWhitespaceOnly($value, $message ?: '%s is not a SAML2-compliant URI');

            // If it doesn't have a scheme, it's not an absolute URI
            BaseAssert::regex($value, self::$scheme_regex, $message ?: '%s is not a SAML2-compliant URI');
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException($e->getMessage());
        }
    }


    /**
     * @param string $value
     * @param string $message
     */
    private static function validEntityID(string $value, string $message = ''): void
    {
        static::validURI($value);

        try {
            BaseAssert::maxLength(
                $value,
                C::ENTITYID_MAX_LENGTH,
                sprintf('An entityID cannot be longer than %d characters.', C::ENTITYID_MAX_LENGTH),
            );
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException($e->getMessage());
        }
    }
}
