<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMElement;
use Exception;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Utils as XMLUtils;

use function array_key_exists;
use function is_null;

/**
 * Base class for all SAML 2 response messages.
 *
 * Implements samlp:StatusResponseType. All of the elements in that type is
 * stored in the \SimpleSAML\SAML2\Message class, and this class is therefore more
 * or less empty. It is included mainly to make it easy to separate requests from
 * responses.
 *
 * Only the 'Code' field is required. The others will be set to null if they
 * aren't present.
 *
 * @package SimpleSAMLphp
 */
abstract class StatusResponse extends Message
{
    /**
     * The ID of the request this is a response to, or null if this is an unsolicited response.
     *
     * @var string|null
     */
    private ?string $inResponseTo = null;


    /**
     * The status code of the response.
     *
     * @var \SimpleSAML\SAML2\XML\samlp\Status
     */
    private Status $status;


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

        $this->status = new Status(
            new StatusCode(Constants::STATUS_SUCCESS),
        );

        if ($xml === null) {
            return;
        }

        if ($xml->hasAttribute('InResponseTo')) {
            $this->inResponseTo = $xml->getAttribute('InResponseTo');
        }

        $status = Status::getChildrenOfClass($xml);
        if (empty($status)) {
            throw new MissingElementException('Missing status code on response.');
        }
        $this->status = array_pop($status);
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
     * @return \SimpleSAML\SAML2\XML\samlp\Status The status code.
     */
    public function getStatus(): Status
    {
        return $this->status;
    }


    /**
     * Set the status code.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\Status $status The status code.
     * @return void
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
    public function toUnsignedXML(): DOMElement
    {
        $root = parent::toUnsignedXML();

        if ($this->inResponseTo !== null) {
            $root->setAttribute('InResponseTo', $this->inResponseTo);
        }

        $this->status->toXML($root);

        return $root;
    }
}
