<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use Webmozart\Assert\Assert;

/**
 * SAML StatusMessage data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class StatusMessage extends AbstractSamlpElement
{
    /** @var string */
    protected $message;


    /**
     * Initialize a samlp:StatusMessage
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->setMessage($message);
    }


    /**
     * Collect the message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }


    /**
     * Set the value of the message-property
     *
     * @param string $message
     * @return void
     */
    private function setMessage(string $message): void
    {
        $this->message = $message;
    }


    /**
     * Convert XML into a StatusMessage
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\samlp\StatusMessage
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'StatusMessage');
        Assert::same($xml->namespaceURI, StatusMessage::NS);

        return new self($xml->textContent);
    }


    /**
     * Convert this StatusMessage to XML.
     *
     * @param \DOMElement|null $element The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this StatusMessage.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->message;

        return $e;
    }
}
