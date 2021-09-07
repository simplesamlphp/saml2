<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use SimpleSAML\SAML2\Constants;

/**
 * A SAML error used by an intermediary to indicate that none of the identity providers
 *   in an IDPList are supported by the intermediary.
 *
 * @package simplesamlphp/saml2
 */
class NoSupportedIDPException extends AbstractProtocolException
{
    /**
     * NoSupportedIDPException constructor.
     *
     * @param string $responsible A string telling who is responsible for this error. Can be one of the following:
     *   - \SimpleSAML\SAML2\Constants::STATUS_RESPONDER: in case the error is caused by this SAML responder.
     *   - \SimpleSAML\SAML2\Constants::STATUS_REQUESTER: in case the error is caused by the SAML requester.
     * @param string|null $message A short message explaining why this error happened.
     */
    public function __construct(string $responsible, string $message = null)
    {
        parent::__construct($responsible, Constants::STATUS_NO_SUPPORTED_IDP, $message);
    }
}
