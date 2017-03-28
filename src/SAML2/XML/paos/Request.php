<?php

namespace SAML2\XML\paos;

use SAML2\Constants;
use SAML2\Utils;

/**
 * Class representing the PAOS Request element.
 *
 * @version $Id$
 */
class Request
{
    /**
     * The URL we should deliver the response to.
     *
     * @var string
     */
    public $responseConsumerURL;

    /**
     * The service.
     *
     * @var string
     */
    public $service;

    /**
     * The message ID.
     *
     * @var string|null
     */
    public $messageID;

    /**
     * Create a PAOS Request element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttributeNS(Constants::NS_SOAP, 'mustUnderstand')) {
            throw new Exception('Missing soap-env:mustUnderstand attribute in <paos:Request>.');
        } elseif ($xml->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand') !== '1') {
            throw new Exception('Invalid value of soap-env:mustUnderstand attribute in <paos:Request>.');
        }

        if (!$xml->hasAttributeNS(Constants::NS_SOAP, 'actor')) {
            throw new Exception('Missing soap-env:mustUnderstand attribute in <paos:Request>.');
        } elseif ($xml->getAttributeNS(Constants::NS_SOAP, 'actor') !== 'http://schemas.xmlsoap.org/soap/actor/next') {
            throw new Exception('Invalid value of soap-env:actor attribute in <paos:Request>.');
        }

        if (!$xml->hasAttribute('responseConsumerURL')) {
            throw new Exception('Missing responseConsumerURL attribute in <paos:Request>.');
        }
        $this->responseConsumerURL = $xml->getAttribute('responseConsumerURL');

        if (!$xml->hasAttribute('service')) {
            throw new Exception('Missing service attribute in <paos:Request>.');
        }
        $this->service = $xml->getAttribute('service');

        if ($xml->hasAttribute('messageID')) {
            $this->messageID = $xml->getAttribute('messageID');
        }
    }

    /**
     * Convert this PAOS Request to XML.
     *
     * @param \DOMElement $parent The element we should append this element to.
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_string($this->responseConsumerURL)');
        assert('is_string($this->service)');
        assert('is_string($this->messageID) || is_null($this->messageID)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS('urn:liberty:paos:2003-08', 'paos:Request');
        $parent->appendChild($e);

        $e->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:mustUnderstand', '1');
        $e->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:actor', 'http://schemas.xmlsoap.org/soap/actor/next');

        $e->setAttribute('responseConsumerURL', $this->responseConsumerURL);
        $e->setAttribute('service', $this->service);

        if ($this->messageID !== null) {
            $e->setAttribute('messageID', $this->messageID);
        }

        return $e;
    }
}
