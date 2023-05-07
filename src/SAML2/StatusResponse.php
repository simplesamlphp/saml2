<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMElement;
use Exception;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Utils as XMLUtils;

use function array_key_exists;
use function is_null;

/**
 * Base class for all SAML 2 response messages.
 *
 * Implements samlp:StatusResponseType. All of the elements in that type is
 * stored in the \SAML2\Message class, and this class is therefore more
 * or less empty. It is included mainly to make it easy to separate requests from
 * responses.
 *
 * The status code is represented as an array on the following form:
 * [
 *   'Code' => '<top-level status code>',
 *   'SubCode' => '<second-level status code>',
 *   'Message' => '<status message>',
 * ]
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
     * @var array
     */
    private array $status;


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

        $this->status = [
            'Code' => Constants::STATUS_SUCCESS,
            'SubCode' => null,
            'Message' => null,
        ];

        if ($xml === null) {
            return;
        }

        if ($xml->hasAttribute('InResponseTo')) {
            $this->inResponseTo = $xml->getAttribute('InResponseTo');
        }

        $xpCache = XPath::getXPath($xml);
        /** @var \DOMElement[] $status */
        $status = XPath::xpQuery($xml, './saml_protocol:Status', $xpCache);
        if (empty($status)) {
            throw new MissingElementException('Missing status code on response.');
        }

        $xpCache = XPath::getXPath($status[0]);
        /** @var \DOMElement[] $statusCode */
        $statusCode = XPath::xpQuery($status[0], './saml_protocol:StatusCode', $xpCache);
        if (empty($statusCode)) {
            throw new MissingElementException('Missing status code in status element.');
        }
        $this->status['Code'] = $statusCode[0]->getAttribute('Value');

        /** @var \DOMElement[] $message */
        $message = XPath::xpQuery($status[0], './saml_protocol:StatusMessage', $xpCache);
        if (!empty($message)) {
            $this->status['Message'] = trim($message[0]->textContent);
        }

        $xpCache = XPath::getXPath($statusCode[0]);
        /** @var \DOMElement[] $subCode */
        $subCode = XPath::xpQuery($statusCode[0], './saml_protocol:StatusCode', $xpCache);
        if (!empty($subCode)) {
            $this->status['SubCode'] = $subCode[0]->getAttribute('Value');
        }
    }


    /**
     * Determine whether this is a successful response.
     *
     * @return bool true if the status code is success, false if not.
     */
    public function isSuccess(): bool
    {
        Assert::keyExists($this->status, "Code");

        return $this->status['Code'] === Constants::STATUS_SUCCESS;
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
     * @return array The status code.
     */
    public function getStatus(): array
    {
        return $this->status;
    }


    /**
     * Set the status code.
     *
     * @param array $status The status code.
     * @return void
     */
    public function setStatus(array $status): void
    {
        Assert::keyExists($status, "Code", 'Cannot set status without a Code key in the array.');

        $this->status = $status;
        if (!array_key_exists('SubCode', $status)) {
            $this->status['SubCode'] = null;
        }
        if (!array_key_exists('Message', $status)) {
            $this->status['Message'] = null;
        }
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

        $status = $this->document->createElementNS(Constants::NS_SAMLP, 'Status');
        $root->appendChild($status);

        $statusCode = $this->document->createElementNS(Constants::NS_SAMLP, 'StatusCode');
        $statusCode->setAttribute('Value', $this->status['Code']);
        $status->appendChild($statusCode);

        if (!is_null($this->status['SubCode'])) {
            $subStatusCode = $this->document->createElementNS(Constants::NS_SAMLP, 'StatusCode');
            $subStatusCode->setAttribute('Value', $this->status['SubCode']);
            $statusCode->appendChild($subStatusCode);
        }

        if (!is_null($this->status['Message'])) {
            XMLUtils::addString($status, Constants::NS_SAMLP, 'StatusMessage', $this->status['Message']);
        }

        return $root;
    }
}
