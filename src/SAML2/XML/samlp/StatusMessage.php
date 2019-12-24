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

class StatusMessage extends \SAML2\XML\AbstractConvertable
{
    /** @var string */
    private $message;


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
        Assert::stringNotEmpty($this->message);

        return $this->message;
    }


    /**
     * Set the value of the message-property
     *
     * @param string $message
     * @return void
     */
    public function setMessage(string $message): void
    {
        $message = trim($message);
        Assert::stringNotEmpty($message);
        $this->message = $message;
    }


    /**
     * Convert XML into a StatusMessage
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\samlp\StatusMessage
     */
    public static function fromXML(DOMElement $xml): object
    {
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
        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS(Constants::NS_SAMLP, 'samlp:StatusMessage');
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS(Constants::NS_SAMLP, 'samlp:StatusMessage');
            $parent->appendChild($e);
        }

        $e->textContent = $this->message;
        return $e;
    }
}
