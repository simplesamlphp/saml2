<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;

use function sprintf;

/**
 * @package simplesamlphp/saml2
 */
trait EntityIDTrait
{
    /**
     * @param string $value
     * @param string $message
     */
    protected static function validEntityID(string $value, string $message = ''): void
    {
        static::validSAMLAnyURI($value);

        try {
            parent::notWhitespaceOnly($value);
            parent::maxLength(
                $value,
                C::ENTITYID_MAX_LENGTH,
                sprintf('An entityID cannot be longer than %d characters.', C::ENTITYID_MAX_LENGTH),
            );
        } catch (AssertionFailedException $e) {
            throw new ProtocolViolationException($e->getMessage());
        }
    }
}
