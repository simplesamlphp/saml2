<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Exception\Protocol;

use RuntimeException;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;

/**
 * Class for representing a SAML 2 error.
 *
 * @package simplesaml\saml2
 */
abstract class AbstractProtocolException extends RuntimeException
{
    /**
     * The top-level status code.
     *
     * @var string
     */
    protected string $status;

    /**
     * The second-level status code, or NULL if no second-level status code is defined.
     *
     * @var string|null
     */
    protected ?string $subStatus;

    /**
     * The status message, or NULL if no status message is defined.
     *
     * @var string|null
     */
    protected ?string $statusMessage;


    /**
     * Create a SAML 2 error.
     *
     * @param string $status  The top-level status code.
     * @param string|null $subStatus  The second-level status code.
     *   Can be NULL, in which case there is no second-level status code.
     * @param string|null $statusMessage  The status message.
     *   Can be NULL, in which case there is no status message.
     */
    public function __construct(
        string $status,
        ?string $subStatus = null,
        ?string $statusMessage = null
    ) {
        Assert::oneOf(
            $status,
            [
                C::STATUS_SUCCESS,
                C::STATUS_REQUESTER,
                C::STATUS_RESPONDER,
                C::STATUS_VERSION_MISMATCH,
            ],
            'Invalid top-level status code',
            ProtocolViolationException::class
        );

        $st = self::shortStatus($status);
        if ($subStatus !== null) {
            $st .= '/' . $this->shortStatus($subStatus);
        }
        if ($statusMessage !== null) {
            $st .= ': ' . $statusMessage;
        }
        parent::__construct($st);

        $this->status = $status;
        $this->subStatus = $subStatus;
        $this->statusMessage = $statusMessage;
    }


    /**
     * Get the top-level status code.
     *
     * @return string  The top-level status code.
     */
    public function getStatus(): string
    {
        return $this->status;
    }


    /**
     * Get the second-level status code.
     *
     * @return string|null  The second-level status code or NULL if no second-level status code is present.
     */
    public function getSubStatus(): ?string
    {
        return $this->subStatus;
    }


    /**
     * Get the status message.
     *
     * @return string|null  The status message or NULL if no status message is present.
     */
    public function getStatusMessage(): ?string
    {
        return $this->statusMessage;
    }


    /**
     * Create a short version of the status code.
     *
     * Remove the 'urn:oasis:names:tc:SAML:2.0:status:'-prefix of status codes
     *   if it is present.
     *
     * @param string $status  The status code.
     * @return string  A shorter version of the status code.
     */
    private function shortStatus(string $status): string
    {
        return preg_filter(sprintf('/^%s/', Constants::STATUS_PREFIX), '', $status) ?? $status;
    }
}
