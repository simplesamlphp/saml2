<?php

namespace SAML2\XML\ecp;

/**
 * Class representing the ECP RelayState element.
 *
 * @version $Id$
 */
class RelayState
{
    /**
     * The RelayState.
     *
     * @var string
     */
    public $RelayState;

    /**
     * Create a ECP RelayState element.
     *
     * @param DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttributeNS(Constants::NS_SOAP, 'mustUnderstand')) {
            throw new Exception('Missing soap-env:mustUnderstand attribute in <ecp:RelayState>.');
        } elseif ($xml->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand') !== '1') {
            throw new Exception('Invalid value of soap-env:mustUnderstand attribute in <ecp:RelayState>.');
        }

        if (!$xml->hasAttributeNS(Constants::NS_SOAP, 'actor')) {
            throw new Exception('Missing soap-env:mustUnderstand attribute in <ecp:RelayState>.');
        } elseif ($xml->getAttributeNS(Constants::NS_SOAP, 'actor') !== 'http://schemas.xmlsoap.org/soap/actor/next') {
            throw new Exception('Invalid value of soap-env:actor attribute in <ecp:RelayState>.');
        }

        $this->RelayState = $xml->textContent;
    }

    /**
     * Convert this ECP RelayState to XML.
     *
     * @param DOMElement $parent The element we should append this element to.
     */
    public function toXML(DOMElement $parent)
    {
        assert('is_string($this->RelayState)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_ECP, 'ecp:RelayState');
        $parent->appendChild($e);

        $e->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:mustUnderstand', '1');
        $e->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:actor', 'http://schemas.xmlsoap.org/soap/actor/next');

        $e->appendChild($doc->createTextNode($this->RelayState));

        return $e;
    }
}
