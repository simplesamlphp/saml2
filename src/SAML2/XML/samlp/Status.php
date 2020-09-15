<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * SAML Status data type.
 *
 * @package simplesamlphp/saml2
 */
final class Status extends AbstractSamlpElement
{
    /** @var \SimpleSAML\SAML2\XML\samlp\StatusCode */
    protected StatusCode $statusCode;

    /** @var string|null */
    protected ?string $statusMessage;

    /** @var \SimpleSAML\SAML2\XML\samlp\StatusDetail[] */
    protected array $statusDetails = [];


    /**
     * Initialize a samlp:Status
     *
     * @param \SimpleSAML\SAML2\XML\samlp\StatusCode $statusCode
     * @param string|null $statusMessage
     * @param \SimpleSAML\SAML2\XML\samlp\StatusDetail[] $statusDetails
     */
    public function __construct(StatusCode $statusCode, ?string $statusMessage = null, array $statusDetails = [])
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
        $this->statusCode = $statusCode;
    }


    /**
     * Collect the value of the statusMessage
     *
     * @return string
     */
    public function getStatusMessage(): ?string
    {
        return $this->statusMessage;
    }


    /**
     * Set the value of the statusMessage property
     * @param string|null $statusMessage
     *
     */
    private function setStatusMessage(?string $statusMessage): void
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
     * @throws \SimpleSAML\Assert\AssertionFailedException if the supplied array contains anything other than StatusDetail objects
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
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     * @throws \SimpleSAML\XML\Exception\MissingElementException if one of the mandatory child-elements is missing
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Status', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Status::NS, InvalidDOMElementException::class);

        $statusCode = StatusCode::getChildrenOfClass($xml);
        Assert::minCount($statusCode, 1, MissingElementException::class);
        Assert::count($statusCode, 1, TooManyElementsException::class);

        $statusMessage = XMLUtils::extractStrings($xml, AbstractSamlpElement::NS, 'StatusMessage');
        Assert::maxCount($statusMessage, 1, TooManyElementsException::class);

        $statusDetails = StatusDetail::getChildrenOfClass($xml);

        return new self(
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

        $this->statusCode->toXML($e);

        if (!is_null($this->statusMessage)) {
            XMLUtils::addString($e, AbstractSamlpElement::NS, 'samlp:StatusMessage', $this->statusMessage);
        }

        foreach ($this->statusDetails as $sd) {
            if (!$sd->isEmptyElement()) {
                $sd->toXML($e);
            }
        }

        return $e;
    }
}
