<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\Issuer;

/**
 * Base class for all SAML 2 response messages.
 *
 * Implements samlp:StatusResponseType. All of the elements in that type is
 * stored in the \SimpleSAML\SAML2\Message class, and this class is therefore more
 * or less empty. It is included mainly to make it easy to separate requests from
 * responses.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractStatusResponse extends AbstractMessage
{
    /**
     * Constructor for SAML 2 response messages.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\Status $status
     * @param \DateTimeImmutable $issueInstant
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string $version
     * @param string|null $inResponseTo
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions|null $extensions
     *
     * @throws \Exception
     */
    protected function __construct(
        protected Status $status,
        DateTimeImmutable $issueInstant,
        ?Issuer $issuer = null,
        ?string $id = null,
        string $version = '2.0',
        protected ?string $inResponseTo = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
    ) {
        Assert::nullOrValidNCName($inResponseTo); // Covers the empty string

        parent::__construct($issuer, $id, $version, $issueInstant, $destination, $consent, $extensions);
    }


    /**
     * Determine whether this is a successful response.
     *
     * @return bool true if the status code is success, false if not.
     */
    public function isSuccess(): bool
    {
        return $this->status->getStatusCode()->getValue() === C::STATUS_SUCCESS;
    }


    /**
     * Retrieve the ID of the request this is a response to.
     *
     * @return string|null The ID of the request.
     */
    public function getInResponseTo(): ?string
    {
        return $this->inResponseTo;
    }


    /**
     * Retrieve the status code.
     *
     * @return \SimpleSAML\SAML2\XML\samlp\Status The status code.
     */
    public function getStatus(): Status
    {
        return $this->status;
    }


    /**
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        if ($this->getInResponseTo() !== null) {
            $e->setAttribute('InResponseTo', $this->getInResponseTo());
        }

        $this->getStatus()->toXML($e);

        return $e;
    }
}
