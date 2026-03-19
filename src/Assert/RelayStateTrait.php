<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Assert;

use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * @package simplesamlphp/saml2
 */
trait RelayStateTrait
{
    /**
     */
    protected static function validRelayState(string $value, string $message = ''): void
    {
        parent::notWhitespaceOnly($value, $message); // Not protocol-defined, but makes zero sense

        /**
         * 3.4.3 RelayState
         *
         * The value MUST NOT exceed 80 bytes in length [..]
         */
        parent::maxLength(
            $value,
            C::MAX_RELAY_STATE_LENGTH,
            $message ?: '%s is not a SAML2.0-compliant RelayState',
            ProtocolViolationException::class,
        );
    }
}
