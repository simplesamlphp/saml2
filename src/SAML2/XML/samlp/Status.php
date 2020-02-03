<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\samlp\StatusCode;
use SAML2\XML\samlp\StatusMessage;
use SAML2\XML\samlp\StatusDetail;
use Webmozart\Assert\Assert;

/**
 * SAML Status data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class Status extends AbstractSamlpElement
{
    /** @var \SAML2\XML\samlp\StatusCode */
    protected $statusCode;

    /** @var \SAML2\XML\samlp\StatusMessage|null */
    protected $statusMessage = null;

    /** @var \SAML2\XML\samlp\StatusDetail[] */
    protected $statusDetails = [];


    /**
     * Initialize a samlp:Status
     *
     * @param \SAML2\XML\samlp\StatusCode $statusCode
     * @param \SAML2\XML\samlp\StatusMessage|null $statusMessage
     * @param \SAML2\XML\samlp\StatusDetail[] $statusDetails
     */
    public function __construct(StatusCode $statusCode, ?StatusMessage $statusMessage = null, array $statusDetails = [])
    {
        $this->setStatusCode($statusCode);
        $this->setStatusMessage($statusMessage);
        $this->setStatusDetails($statusDetails);
    }


    /**
     * Collect the StatusCode
     *
     * @return \SAML2\XML\samlp\StatusCode
     */
    public function getStatusCode(): StatusCode
    {
        return $this->statusCode;
    }


    /**
     * Set the value of the StatusCode-property
     *
     * @param \SAML2\XML\samlp\StatusCode $statusCode
     * @return void
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    private function setStatusCode(StatusCode $statusCode): void
    {
        $this->statusCode = $statusCode;
    }


    /**
     * Collect the value of the statusMessage
     *
     * @return \SAML2\XML\samlp\StatusMessage|null
     */
    public function getStatusMessage(): ?StatusMessage
    {
        return $this->statusMessage;
    }


    /**
     * Set the value of the statusMessage-property
     * @param \SAML2\XML\samlp\StatusMessage|null $value
     *
     * @return void
     */
    private function setStatusMessage(?StatusMessage $statusMessage): void
    {
        $this->statusMessage = $statusMessage;
    }


    /**
     * Collect the value of the statusDetails-property
     *
     * @return \SAML2\XML\samlp\StatusDetail[]
     */
    public function getStatusDetails(): array
    {
        return $this->statusDetails;
    }


    /**
     * Set the value of the statusDetails-property
     *
     * @param \SAML2\XML\samlp\StatusDetail[] $statusDetails
     * @return void
     * @throws \InvalidArgumentException if the supplied array contains anything other than StatusDetail objects
     */
    private function setStatusDetails(array $statusDetails): void
    {
        Assert::allIsInstanceOf($statusDetails, StatusDetail::class);
        $this->statusDetails = $statusDetails;
    }



    /**
     * Convert XML into a Status
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\samlp\Status
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Status');
        Assert::same($xml->namespaceURI, Status::NS);

        /** @var \DOMElement[] $statusCode */
        $statusCode = Utils::xpQuery($xml, './saml_protocol:StatusCode');
        Assert::count($statusCode, 1);

        $statusCode = StatusCode::fromXML($statusCode[0]);

        /** @var \DOMElement[] $message */
        $message = Utils::xpQuery($xml, './saml_protocol:StatusMessage');
        Assert::maxCount($message, 1);

        $statusMessage = null;
        if (!empty($message)) {
            $statusMessage = StatusMessage::fromXML($message[0]);
        }

        /** @var \DOMElement[] $details */
        $details = Utils::xpQuery($xml, './saml_protocol:StatusDetail');

        $statusDetails = [];
        foreach ($details as $detail) {
            $statusDetails[] = StatusDetail::fromXML($detail);
        }

        return new self(
            $statusCode,
            $statusMessage,
            $statusDetails
        );
    }


    /**
     * Convert this Status to XML.
     *
     * @param \DOMElement|null $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Status.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $this->statusCode->toXML($e);

        if ($this->statusMessage !== null) {
            $this->statusMessage->toXML($e);
        }

        foreach ($this->statusDetails as $sd) {
            if (!$sd->isEmptyElement()) {
                $sd->toXML($e);
            }
        }

        return $e;
    }
}
