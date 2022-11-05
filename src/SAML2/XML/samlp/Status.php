<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

use function array_pop;
use function is_null;

/**
 * SAML Status data type.
 *
 * @package simplesamlphp/saml2
 */
final class Status extends AbstractSamlpElement
{
    /** @var \SimpleSAML\SAML2\XML\samlp\StatusCode */
    protected StatusCode $statusCode;

    /** @var \SimpleSAML\SAML2\XML\samlp\StatusMessage|null */
    protected ?StatusMessage $statusMessage;

    /** @var \SimpleSAML\SAML2\XML\samlp\StatusDetail[] */
    protected array $statusDetails = [];


    /**
     * Initialize a samlp:Status
     *
     * @param \SimpleSAML\SAML2\XML\samlp\StatusCode $statusCode
     * @param \SimpleSAML\SAML2\XML\samlp\StatusMessage|null $statusMessage
     * @param \SimpleSAML\SAML2\XML\samlp\StatusDetail[] $statusDetails
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
     * @return \SimpleSAML\SAML2\XML\samlp\StatusCode
     */
    public function getStatusCode(): StatusCode
    {
        return $this->statusCode;
    }


    /**
     * Set the value of the StatusCode-property
     *
     * @param \SimpleSAML\SAML2\XML\samlp\StatusCode $statusCode
     */
    private function setStatusCode(StatusCode $statusCode): void
    {
        Assert::oneOf(
            $statusCode->getValue(),
            [
                C::STATUS_SUCCESS,
                C::STATUS_REQUESTER,
                C::STATUS_RESPONDER,
                C::STATUS_VERSION_MISMATCH,
            ],
            'Invalid top-level status code:  %s',
            ProtocolViolationException::class
        );

        $this->statusCode = $statusCode;
    }


    /**
     * Collect the value of the statusMessage
     *
     * @return \SimpleSAML\SAML2\XML\samlp\StatusMessage|null
     */
    public function getStatusMessage(): ?StatusMessage
    {
        return $this->statusMessage;
    }


    /**
     * Set the value of the statusMessage property
     * @param \SimpleSAML\SAML2\XML\samlp\StatusMessage|null $statusMessage
     *
     */
    private function setStatusMessage(?StatusMessage $statusMessage): void
    {
        $this->statusMessage = $statusMessage;
    }


    /**
     * Collect the value of the statusDetails property
     *
     * @return \SimpleSAML\SAML2\XML\samlp\StatusDetail[]
     */
    public function getStatusDetails(): array
    {
        return $this->statusDetails;
    }


    /**
     * Set the value of the statusDetails property
     *
     * @param \SimpleSAML\SAML2\XML\samlp\StatusDetail[] $statusDetails
     * @throws \SimpleSAML\Assert\AssertionFailedException
     *   if the supplied array contains anything other than StatusDetail objects
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
     * @return \SimpleSAML\SAML2\XML\samlp\Status
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     * @throws \SimpleSAML\XML\Exception\MissingElementException
     *   if one of the mandatory child-elements is missing
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Status', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Status::NS, InvalidDOMElementException::class);

        $statusCode = StatusCode::getChildrenOfClass($xml);
        Assert::minCount($statusCode, 1, MissingElementException::class);
        Assert::count($statusCode, 1, TooManyElementsException::class);

        $statusMessage = StatusMessage::getChildrenOfClass($xml);
        Assert::maxCount($statusMessage, 1, TooManyElementsException::class);

        $statusDetails = StatusDetail::getChildrenOfClass($xml);

        return new static(
            array_pop($statusCode),
            array_pop($statusMessage),
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

        $this->getStatusCode()->toXML($e);

        $this->getStatusMessage()?->toXML($e);

        foreach ($this->getStatusDetails() as $sd) {
            if (!$sd->isEmptyElement()) {
                $sd->toXML($e);
            }
        }

        return $e;
    }
}
