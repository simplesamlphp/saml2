<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use SAML2\XML\ExtensionsTrait;
use SAML2\XML\saml\Issuer;

/**
 * Base class for all SAML 2 response messages.
 *
 * Implements samlp:StatusResponseType. All of the elements in that type is
 * stored in the \SAML2\Message class, and this class is therefore more
 * or less empty. It is included mainly to make it easy to separate requests from
 * responses.
 *
 * @package SimpleSAMLphp
 */
abstract class AbstractStatusResponse extends AbstractMessage
{
    use ExtensionsTrait;

    /**
     * The ID of the request this is a response to, or null if this is an unsolicited response.
     *
     * @var string|null
     */
    protected $inResponseTo;


    /**
     * The status code of the response.
     *
     * @var \SAML2\XML\samlp\Status
     */
    protected $status;


    /**
     * Constructor for SAML 2 response messages.
     *
     * @param \SAML2\XML\samlp\Status $status
     * @param \SAML2\XML\saml\Issuer $issuer
     * @param string $id
     * @param int $issueInstant
     * @param string $inResponseTo
     * @param string|null $destination
     * @param string|null $consent
     * @param \SAML2\XML\samlp\Extensions $extensions
     * @param string|null $relayState
     *
     * @throws \Exception
     */
    protected function __construct(
        Status $status,
        ?Issuer $issuer = null,
        ?string $id = null,
        ?int $issueInstant = null,
        ?string $inResponseTo = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
        ?string $relayState = null
    ) {
        parent::__construct($issuer, $id, $issueInstant, $destination, $consent, $extensions, $relayState);

        $this->setStatus($status);
        $this->setInResponseTo($inResponseTo);
    }


    /**
     * Determine whether this is a successful response.
     *
     * @return bool true if the status code is success, false if not.
     */
    public function isSuccess(): bool
    {
        return $this->status->getStatusCode()->getValue() === Constants::STATUS_SUCCESS;
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
     * Set the ID of the request this is a response to.
     *
     * @param string|null $inResponseTo The ID of the request.
     * @return void
     */
    protected function setInResponseTo(?string $inResponseTo): void
    {
        $this->inResponseTo = $inResponseTo;
    }


    /**
     * Retrieve the status code.
     *
     * @return \SAML2\XML\samlp\Status The status code.
     */
    public function getStatus(): Status
    {
        return $this->status;
    }


    /**
     * Set the status code.
     *
     * @param \SAML2\XML\samlp\Status $status The status code.
     * @return void
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    protected function setStatus(Status $status): void
    {
        $this->status = $status;
    }


    /**
     * Convert status response message to an XML element.
     *
     * @inheritDoc
     * @return \DOMElement This status response.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $parent = parent::toXML();

        if ($this->inResponseTo !== null) {
            $parent->setAttribute('InResponseTo', $this->inResponseTo);
        }

        $this->status->toXML($parent);

        return $parent;
    }
}
