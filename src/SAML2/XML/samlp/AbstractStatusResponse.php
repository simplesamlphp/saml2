<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use Webmozart\Assert\Assert;

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
    /**
     * The ID of the request this is a response to, or null if this is an unsolicited response.
     *
     * @var string|null
     */
    private $inResponseTo;


    /**
     * The status code of the response.
     *
     * @var \SAML2\XML\samlp\Status
     */
    private $status;


    /**
     * Constructor for SAML 2 response messages.
     *
     * @param string $tagName The tag name of the root element.
     * @param \DOMElement|null $xml The input message.
     * @throws \Exception
     */
    protected function __construct(string $tagName, DOMElement $xml = null)
    {
        parent::__construct($tagName, $xml);

        if ($xml === null) {
            $this->status = new Status(
                new StatusCode(Constants::STATUS_SUCCESS)
            );

            return;
        }

        if ($xml->hasAttribute('InResponseTo')) {
            $this->inResponseTo = $xml->getAttribute('InResponseTo');
        }
        /** @var \DOMElement[] $status */
        $status = Utils::xpQuery($xml, './saml_protocol:Status');
        if (empty($status)) {
            throw new \Exception('Missing status code on response.');
        }
        $this->status = Status::fromXML($status[0]);
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
    public function setInResponseTo(string $inResponseTo = null): void
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
    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }


    /**
     * Convert status response message to an XML element.
     *
     * @return \DOMElement This status response.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        Assert::null($parent);

        $parent = parent::toXML();

        if ($this->inResponseTo !== null) {
            $parent->setAttribute('InResponseTo', $this->inResponseTo);
        }

        $this->status->toXML($parent);

        return $parent;
    }
}
