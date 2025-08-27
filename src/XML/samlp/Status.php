<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XMLSchema\Exception\{InvalidDOMElementException, MissingElementException, TooManyElementsException};

use function array_pop;
use function strval;

/**
 * SAML Status data type.
 *
 * @package simplesamlphp/saml2
 */
final class Status extends AbstractSamlpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize a samlp:Status
     *
     * @param \SimpleSAML\SAML2\XML\samlp\StatusCode $statusCode
     * @param \SimpleSAML\SAML2\XML\samlp\StatusMessage|null $statusMessage
     * @param \SimpleSAML\SAML2\XML\samlp\StatusDetail[] $statusDetails
     */
    public function __construct(
        protected StatusCode $statusCode,
        protected ?StatusMessage $statusMessage = null,
        protected array $statusDetails = [],
    ) {
        Assert::oneOf(
            strval($statusCode->getValue()),
            [
                C::STATUS_SUCCESS,
                C::STATUS_REQUESTER,
                C::STATUS_RESPONDER,
                C::STATUS_VERSION_MISMATCH,
            ],
            'Invalid top-level status code:  %s',
            ProtocolViolationException::class,
        );
        Assert::maxCount($statusDetails, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($statusDetails, StatusDetail::class);
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
     * Collect the value of the statusMessage
     *
     * @return \SimpleSAML\SAML2\XML\samlp\StatusMessage|null
     */
    public function getStatusMessage(): ?StatusMessage
    {
        return $this->statusMessage;
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
     * Convert XML into a Status
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     * @throws \SimpleSAML\XMLSchema\Exception\MissingElementException
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
            $statusDetails,
        );
    }


    /**
     * Convert this Status to XML.
     *
     * @param \DOMElement|null $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Status.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
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
